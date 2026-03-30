<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Enums\ServerRole;
use App\Enums\SshKeyPurpose;
use App\Exceptions\RetryStepException;
use App\Models\Infrastructure;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use Closure;
use RuntimeException;
use Symfony\Component\Process\Process;

final readonly class ScpToBastion implements StepHandler
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

    public function handle(Infrastructure $infrastructure): void
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

        $nodeKey = ($this->sshKeyQuery)()
            ->byInfrastructure($infrastructure)
            ->byPurpose(SshKeyPurpose::Node)
            ->firstOrFail();

        $keyFile = tempnam(sys_get_temp_dir(), 'kuven_bastion_key_');

        if ($keyFile === false || file_put_contents($keyFile, $bastionKey->private_key) === false) { // @codeCoverageIgnore
            throw new RuntimeException('Failed to write bastion key to temporary file.'); // @codeCoverageIgnore
        } // @codeCoverageIgnore

        chmod($keyFile, 0600);

        $nodeKeyFile = tempnam(sys_get_temp_dir(), 'kuven_node_key_');

        if ($nodeKeyFile === false || file_put_contents($nodeKeyFile, $nodeKey->private_key) === false) { // @codeCoverageIgnore
            @unlink($keyFile); // @codeCoverageIgnore

            throw new RuntimeException('Failed to write node key to temporary file.'); // @codeCoverageIgnore
        } // @codeCoverageIgnore

        chmod($nodeKeyFile, 0600);

        $sshUser = $infrastructure->cloudProvider->type->sshUser();
        $homeDir = $sshUser === 'root' ? '/root' : "/home/{$sshUser}";

        try {
            $sshOptions = [
                '-o', 'StrictHostKeyChecking=no',
                '-o', 'UserKnownHostsFile=/dev/null',
                '-o', 'BatchMode=yes',
                '-i', $keyFile,
            ];

            $this->scp(
                $sshOptions,
                base_path('infrastructure/playbooks').'/',
                "{$sshUser}@{$bastion->ipv4}:{$homeDir}/playbooks",
                recursive: true,
            );

            $this->scp(
                $sshOptions,
                $nodeKeyFile,
                "{$sshUser}@{$bastion->ipv4}:{$homeDir}/.ssh/node_key",
            );
        } finally {
            @unlink($keyFile);
            @unlink($nodeKeyFile);
        }
    }

    /**
     * @param  list<string>  $sshOptions
     */
    private function scp(array $sshOptions, string $source, string $destination, bool $recursive = false): void
    {
        $command = ['scp'];

        if ($recursive) {
            $command[] = '-r';
        }

        $command = [...$command, ...$sshOptions, $source, $destination];

        $process = ($this->processFactory)($command);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            $error = $process->getErrorOutput();

            if (str_contains($error, 'Connection refused') || str_contains($error, 'Connection timed out') || str_contains($error, 'No route to host')) {
                throw new RetryStepException('SCP connection failed (server may still be booting): '.$error);
            }

            throw new RuntimeException('SCP failed: '.$error);
        }
    }
}
