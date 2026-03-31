<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ServerRole;
use App\Enums\SshKeyPurpose;
use App\Exceptions\RetryStepException;
use App\Models\Infrastructure;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

final readonly class BastionSshExecutor
{
    /** @var Closure(list<string>): Process */
    private Closure $processFactory;

    /**
     * @param  Closure(list<string>): Process|null  $processFactory
     */
    public function __construct(
        private ServerQuery $serverQuery,
        private SshKeyQuery $sshKeyQuery,
        ?Closure $processFactory = null,
    ) {
        $this->processFactory = $processFactory ?? fn (array $command): Process => new Process($command);
    }

    /**
     * @param  Closure(string, string): void|null  $onOutput
     */
    public function execute(Infrastructure $infrastructure, string $command, int $timeout = 600, ?Closure $onOutput = null): string
    {
        [$keyFile, $sshUser, $bastionIp] = $this->prepareConnection($infrastructure);

        Log::info("[{$infrastructure->name}] SSH: {$command}");

        try {
            $sshCommand = [
                'ssh',
                '-o', 'StrictHostKeyChecking=no',
                '-o', 'UserKnownHostsFile=/dev/null',
                '-o', 'BatchMode=yes',
                '-o', 'ServerAliveInterval=30',
                '-o', 'ServerAliveCountMax=5',
                '-i', $keyFile,
                "{$sshUser}@{$bastionIp}",
                $command,
            ];

            $process = ($this->processFactory)($sshCommand);
            $process->setTimeout($timeout);

            $process->run(function (string $type, string $buffer) use ($infrastructure, $onOutput): void {
                $this->streamLog($infrastructure, $type, $buffer);
                if ($onOutput !== null) {
                    $onOutput($type, $buffer);
                }
            });

            if (! $process->isSuccessful()) {
                $stderr = $process->getErrorOutput();
                $stdout = $process->getOutput();
                $fullOutput = trim($stdout."\n".$stderr);

                if (str_contains($stderr, 'Connection refused') || str_contains($stderr, 'Connection timed out')) {
                    throw new RetryStepException("SSH connection failed: {$stderr}");
                }

                throw new RuntimeException("SSH command failed (exit code {$process->getExitCode()}): {$fullOutput}");
            }

            return $process->getOutput();
        } finally {
            @unlink($keyFile);
        }
    }

    public function scp(Infrastructure $infrastructure, string $localPath, string $remotePath, bool $recursive = false): void
    {
        [$keyFile, $sshUser, $bastionIp] = $this->prepareConnection($infrastructure);

        Log::info("[{$infrastructure->name}] SCP: {$localPath} → {$sshUser}@{$bastionIp}:{$remotePath}");

        try {
            $command = ['scp'];

            if ($recursive) {
                $command[] = '-r';
            }

            $command = [
                ...$command,
                '-o', 'StrictHostKeyChecking=no',
                '-o', 'UserKnownHostsFile=/dev/null',
                '-o', 'BatchMode=yes',
                '-i', $keyFile,
                $localPath,
                "{$sshUser}@{$bastionIp}:{$remotePath}",
            ];

            $process = ($this->processFactory)($command);
            $process->setTimeout(120);
            $process->run();

            if (! $process->isSuccessful()) {
                $error = $process->getErrorOutput();

                if (str_contains($error, 'Connection refused') || str_contains($error, 'Connection timed out')) {
                    throw new RetryStepException("SCP connection failed: {$error}");
                }

                throw new RuntimeException("SCP failed: {$error}");
            }
        } finally {
            @unlink($keyFile);
        }
    }

    /**
     * @return array{string, string, string} [keyFile, sshUser, bastionIp]
     */
    private function prepareConnection(Infrastructure $infrastructure): array
    {
        $bastion = ($this->serverQuery)()
            ->byInfrastructure($infrastructure)
            ->byRole(ServerRole::Bastion)
            ->firstOrFail();

        if ($bastion->ipv4 === null) {
            throw new RuntimeException('Bastion server has no IP address.');
        }

        $bastionKey = ($this->sshKeyQuery)()
            ->byInfrastructure($infrastructure)
            ->byPurpose(SshKeyPurpose::Bastion)
            ->firstOrFail();

        $keyFile = tempnam(sys_get_temp_dir(), 'kuven_ssh_');

        if ($keyFile === false || file_put_contents($keyFile, $bastionKey->private_key) === false) { // @codeCoverageIgnore
            throw new RuntimeException('Failed to write SSH key to temporary file.'); // @codeCoverageIgnore
        } // @codeCoverageIgnore

        chmod($keyFile, 0600);

        $sshUser = $infrastructure->cloudProvider->type->sshUser();

        return [$keyFile, $sshUser, $bastion->ipv4];
    }

    private function streamLog(Infrastructure $infrastructure, string $type, string $buffer): void
    {
        $logPath = "bastion-logs/{$infrastructure->id}.log";
        $prefix = $type === Process::ERR ? '[STDERR]' : '[STDOUT]';

        $lines = explode("\n", rtrim($buffer));
        $entry = implode("\n", array_map(
            fn (string $line): string => $line !== '' ? "{$prefix} {$line}" : '',
            $lines,
        ));

        Storage::disk('local')->append($logPath, $entry);
    }
}
