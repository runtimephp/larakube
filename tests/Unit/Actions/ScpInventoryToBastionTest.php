<?php

declare(strict_types=1);

use App\Actions\ScpInventoryToBastion;
use App\Enums\ServerRole;
use App\Enums\ServerStatus;
use App\Models\Infrastructure;
use App\Models\Server;
use App\Models\SshKey;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use App\Services\BastionSshExecutor;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

test('scps inventory file to bastion',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

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

        // Create inventory file
        Storage::disk('local')->put("inventories/{$infrastructure->id}.ini", '[bastion]');

        $capturedCommands = [];
        $processFactory = function (array $command) use (&$capturedCommands): Process {
            $capturedCommands[] = $command;
            $process = Process::fromShellCommandline('true');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $action = new ScpInventoryToBastion($ssh);
        $action->handle($infrastructure);

        expect($capturedCommands)->not->toBeEmpty();

        $commandStr = implode(' ', $capturedCommands[0]);
        expect($commandStr)->toContain('scp')
            ->and($commandStr)->toContain('192.168.1.1')
            ->and($commandStr)->toContain('hosts.ini');
    });

test('uses correct home directory for non-root user',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

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

        Storage::disk('local')->put("inventories/{$infrastructure->id}.ini", '[bastion]');

        $capturedCommands = [];
        $processFactory = function (array $command) use (&$capturedCommands): Process {
            $capturedCommands[] = $command;
            $process = Process::fromShellCommandline('true');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $action = new ScpInventoryToBastion($ssh);
        $action->handle($infrastructure);

        $commandStr = implode(' ', $capturedCommands[0]);
        expect($commandStr)->toContain('/home/ubuntu/playbooks/inventory/hosts.ini');
    });
