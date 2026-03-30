<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Data\CreateServerData;
use App\Enums\ServerRole;
use App\Enums\SshKeyPurpose;
use App\Models\Infrastructure;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use App\Services\CloudInitGenerator;

final readonly class CreateWorkerNodes implements StepHandler
{
    private const int DEFAULT_WORKER_COUNT = 2;

    public function __construct(
        private CreateServer $createServer,
        private CloudInitGenerator $cloudInit,
        private ServerQuery $serverQuery,
        private SshKeyQuery $sshKeyQuery,
    ) {}

    public function handle(Infrastructure $infrastructure): void
    {
        if (($this->serverQuery)()->byInfrastructure($infrastructure)->byRole(ServerRole::Node)->exists()) {
            return;
        }

        $provider = $infrastructure->cloudProvider;
        $spec = $provider->type->workerSpec();

        $sshKeyIds = ($this->sshKeyQuery)()
            ->byInfrastructure($infrastructure)
            ->get()
            ->whereNotNull('external_ssh_key_id')
            ->pluck('external_ssh_key_id')
            ->map(fn (string $id): int|string => is_numeric($id) ? (int) $id : $id)
            ->all();

        $networkId = $infrastructure->networks()->first()?->external_network_id;

        $nodeKey = ($this->sshKeyQuery)()
            ->byInfrastructure($infrastructure)
            ->byPurpose(SshKeyPurpose::Node)
            ->firstOrFail();

        $nodeCloudInit = $this->cloudInit->node($nodeKey->public_key);

        for ($i = 1; $i <= self::DEFAULT_WORKER_COUNT; $i++) {
            $this->createServer->handle($provider, new CreateServerData(
                name: "{$infrastructure->name}-worker-{$i}",
                type: $spec->type,
                image: $spec->image,
                region: $spec->region,
                infrastructure_id: $infrastructure->id,
                role: ServerRole::Node,
                cpus: $spec->cpus,
                memory: $spec->memory,
                disk: $spec->disk,
                sshKeyIds: $sshKeyIds,
                cloudInit: $nodeCloudInit,
                publicIp: false,
                networkId: $networkId,
            ));
        }
    }
}
