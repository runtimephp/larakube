<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Data\CreateServerData;
use App\Enums\ServerRole;
use App\Enums\SshKeyPurpose;
use App\Models\Infrastructure;
use App\Models\Server;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use App\Services\CloudInitGenerator;
use App\Services\CloudProviderFactory;

final readonly class CreateBastion implements StepHandler
{
    public function __construct(
        private CloudProviderFactory $factory,
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
        $serverService = $this->factory->makeServerService($provider->type, $provider->api_token);

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

        $serverData = $serverService->create(new CreateServerData(
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

        Server::query()->create([
            'organization_id' => $infrastructure->organization_id,
            'cloud_provider_id' => $provider->id,
            'infrastructure_id' => $infrastructure->id,
            'external_id' => (string) $serverData->externalId,
            'name' => $serverData->name,
            'status' => $serverData->status,
            'type' => $serverData->type,
            'region' => $serverData->region,
            'ipv4' => $serverData->ipv4,
            'ipv6' => $serverData->ipv6,
            'role' => ServerRole::Bastion,
        ]);
    }
}
