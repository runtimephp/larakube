<?php

declare(strict_types=1);

use App\Actions\RunAnsible;
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

test('throws retry when ansible is not yet installed',
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
            $process = Process::fromShellCommandline('echo "ANSIBLE_NOT_FOUND"');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $action = new RunAnsible($ssh);
        $action->handle($infrastructure);
    })->throws(RetryStepException::class, 'Ansible not yet installed');

test('runs ansible playbook successfully',
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

        $callCount = 0;
        $processFactory = function (array $command) use (&$callCount): Process {
            $callCount++;
            if ($callCount === 1) {
                // which ansible-playbook check
                $process = Process::fromShellCommandline('echo "/usr/bin/ansible-playbook"');
            } else {
                // ansible-playbook run
                $process = Process::fromShellCommandline('echo "PLAY RECAP - ok=5"');
            }
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $action = new RunAnsible($ssh);
        $action->handle($infrastructure);

        expect($callCount)->toBe(2);
    });

test('throws retry when ansible fails with unreachable hosts',
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

        $callCount = 0;
        $processFactory = function (array $command) use (&$callCount): Process {
            $callCount++;
            if ($callCount === 1) {
                $process = Process::fromShellCommandline('echo "/usr/bin/ansible-playbook"');
                $process->run();
            } else {
                $process = Process::fromShellCommandline('echo "unreachable=1" >&2 && exit 1');
                $process->run();
            }

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $action = new RunAnsible($ssh);
        $action->handle($infrastructure);
    })->throws(RetryStepException::class, 'unreachable hosts');

test('throws runtime exception when ansible fails without unreachable',
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

        $callCount = 0;
        $processFactory = function (array $command) use (&$callCount): Process {
            $callCount++;
            if ($callCount === 1) {
                $process = Process::fromShellCommandline('echo "/usr/bin/ansible-playbook"');
                $process->run();
            } else {
                $process = Process::fromShellCommandline('echo "fatal error" >&2 && exit 1');
                $process->run();
            }

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $action = new RunAnsible($ssh);
        $action->handle($infrastructure);
    })->throws(RuntimeException::class, 'SSH command failed');

test('uses correct home directory for non-root user',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        // Multipass uses 'ubuntu' user -> /home/ubuntu
        $infrastructure->cloudProvider->update(['type' => 'multipass']);

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Running,
            'ipv4' => '192.168.1.1',
        ]);

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        $capturedCommands = [];
        $callCount = 0;
        $processFactory = function (array $command) use (&$capturedCommands, &$callCount): Process {
            $callCount++;
            $capturedCommands[] = $command;
            if ($callCount === 1) {
                $process = Process::fromShellCommandline('echo "/usr/bin/ansible-playbook"');
            } else {
                $process = Process::fromShellCommandline('echo "ok"');
            }
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $action = new RunAnsible($ssh);
        $action->handle($infrastructure);

        $ansibleCommand = implode(' ', $capturedCommands[1]);
        expect($ansibleCommand)->toContain('/home/ubuntu/playbooks');
    });
