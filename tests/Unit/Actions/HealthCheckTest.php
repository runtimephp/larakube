<?php

declare(strict_types=1);

use App\Actions\HealthCheck;
use App\Enums\ServerRole;
use App\Enums\ServerStatus;
use App\Exceptions\RetryStepException;
use App\Models\Infrastructure;
use App\Models\Server;
use App\Models\SshKey;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use App\Services\BastionSshExecutor;
use Symfony\Component\Process\Process;

test('passes when all nodes are ready',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Running,
            'ipv4' => '192.168.1.1',
        ]);

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('echo "node-1   Ready   control-plane   10d   v1.31.0"');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $action = new HealthCheck($ssh);
        $action->handle($infrastructure);

        expect(true)->toBeTrue();
    });

test('throws retry when node is not ready',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Running,
            'ipv4' => '192.168.1.1',
        ]);

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('echo "node-1   NotReady   control-plane   10d   v1.31.0"');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $action = new HealthCheck($ssh);
        $action->handle($infrastructure);
    })->throws(RetryStepException::class, 'NotReady');

test('throws retry when output has no Ready status',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Running,
            'ipv4' => '192.168.1.1',
        ]);

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('echo "node-1   Pending   control-plane   10d   v1.31.0"');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $action = new HealthCheck($ssh);
        $action->handle($infrastructure);
    })->throws(RetryStepException::class, 'Not all nodes are Ready');

test('throws retry when no nodes returned',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Running,
            'ipv4' => '192.168.1.1',
        ]);

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('echo ""');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $action = new HealthCheck($ssh);
        $action->handle($infrastructure);
    })->throws(RetryStepException::class, 'No nodes returned');
