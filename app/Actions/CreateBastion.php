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

final readonly class CreateBastion implements StepHandler
{
    public function __construct(
        private CreateServer $createServer,
        private CloudInitGenerator $cloudInit,
        private ServerQuery $serverQuery,
        private SshKeyQuery $sshKeyQuery,
    ) {}

    public function handle(Infrastructure $infrastructure): void
    {
        if (($this->serverQuery)()->byInfrastructure($infrastructure)->byRole(ServerRole::Bastion)->exists()) {
            return;
        }

        $provider = $infrastructure->cloudProvider;

        $bastionKey = ($this->sshKeyQuery)()
            ->byInfrastructure($infrastructure)
            ->byPurpose(SshKeyPurpose::Bastion)
            ->firstOrFail();

        $sshKeyIds = ($this->sshKeyQuery)()
            ->byInfrastructure($infrastructure)
            ->get()
            ->whereNotNull('external_ssh_key_id')
            ->pluck('external_ssh_key_id')
            ->map(fn (string $id): int|string => is_numeric($id) ? (int) $id : $id)
            ->all();

        $cloudInitYaml = $this->cloudInit->bastion(bastionPublicKey: $bastionKey->public_key);
        $spec = $provider->type->bastionSpec();

        $server = $this->createServer->handle($provider, new CreateServerData(
            name: "{$infrastructure->name}-bastion",
            type: $spec->type,
            image: $spec->image,
            region: $spec->region,
            infrastructure_id: $infrastructure->id,
            cpus: $spec->cpus,
            memory: $spec->memory,
            disk: $spec->disk,
            sshKeyIds: $sshKeyIds,
            cloudInit: $cloudInitYaml,
        ));

        $server->update(['role' => ServerRole::Bastion]);
    }
}
