<?php

declare(strict_types=1);

use App\Actions\ScpToBastion;
use App\Enums\ServerRole;
use App\Enums\ServerStatus;
use App\Models\Infrastructure;
use App\Models\Server;
use App\Models\SshKey;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use Symfony\Component\Process\Process;

test('scps playbooks and node key to bastion',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        SshKey::factory()->node()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'private_key' => 'fake-node-private-key',
        ]);

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Running,
            'ipv4' => '192.168.64.10',
        ]);

        $capturedCommands = [];

        $processFactory = function (array $command) use (&$capturedCommands): Process {
            $capturedCommands[] = $command;

            $process = Process::fromShellCommandline('true');
            $process->run();

            return $process;
        };

        $action = new ScpToBastion(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $action->handle($infrastructure);

        expect($capturedCommands)->not->toBeEmpty();

        $allCommandStrings = array_map(fn (array $cmd): string => implode(' ', $cmd), $capturedCommands);
        $hasBastionTarget = false;
        foreach ($allCommandStrings as $cmdStr) {
            if (str_contains($cmdStr, '192.168.64.10')) {
                $hasBastionTarget = true;
            }
        }

        expect($hasBastionTarget)->toBeTrue();
    });

test('throws when bastion has no ip',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        SshKey::factory()->node()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'private_key' => 'fake-node-private-key',
        ]);

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Running,
            'ipv4' => null,
        ]);

        $processFactory = fn (array $command): Process => Process::fromShellCommandline('true');

        $action = new ScpToBastion(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $action->handle($infrastructure);
    })->throws(RuntimeException::class, 'Bastion server has no IP address');
