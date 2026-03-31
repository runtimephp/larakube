<?php

declare(strict_types=1);

use App\Actions\RetrieveKubeconfig;
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

test('retrieves kubeconfig from bastion and stores to disk',
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

        $kubeconfigContent = "apiVersion: v1\nclusters:\n- cluster:\n    server: https://10.0.1.1:6443\n";

        $processFactory = function (array $command) use ($kubeconfigContent): Process {
            $escaped = addcslashes($kubeconfigContent, "'");
            $process = Process::fromShellCommandline("printf '{$escaped}'");
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $action = new RetrieveKubeconfig($ssh);
        $action->handle($infrastructure);

        Storage::disk('local')->assertExists("kubeconfigs/{$infrastructure->id}.conf");
    });
