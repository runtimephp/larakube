<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Enums\ServerRole;
use App\Enums\SshKeyPurpose;
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
        file_put_contents($keyFile, $bastionKey->private_key);
        chmod($keyFile, 0600);

        $nodeKeyFile = tempnam(sys_get_temp_dir(), 'kuven_node_key_');
        file_put_contents($nodeKeyFile, $nodeKey->private_key);
        chmod($nodeKeyFile, 0600);

        try {
            $sshOptions = [
                '-o', 'StrictHostKeyChecking=no',
                '-o', 'UserKnownHostsFile=/dev/null',
                '-i', $keyFile,
            ];

            $this->scp(
                $sshOptions,
                base_path('infrastructure/playbooks'),
                "ubuntu@{$bastion->ipv4}:/home/ubuntu/playbooks",
                recursive: true,
            );

            $this->scp(
                $sshOptions,
                $nodeKeyFile,
                "ubuntu@{$bastion->ipv4}:/home/ubuntu/.ssh/node_key",
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
            throw new RuntimeException('SCP failed: '.$process->getErrorOutput());
        }
    }
}
