<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Queries\InfrastructureQuery;
use App\Queries\ServerQuery;
use App\Services\CloudProviderFactory;

final readonly class SyncServers
{
    public function __construct(
        private CloudProviderFactory $factory,
        private InfrastructureQuery $infrastructureQuery,
        private ServerQuery $serverQuery,
    ) {}

    public function handle(CloudProvider $provider): void
    {
        $infrastructure = ($this->infrastructureQuery)()->byProvider($provider)->first();

        if (! $infrastructure) {
            $infrastructure = Infrastructure::create([
                'organization_id' => $provider->organization_id,
                'cloud_provider_id' => $provider->id,
                'name' => 'Default Infrastructure',
                'description' => 'Auto-created infrastructure for synced servers',
            ]);
        }

        $serverService = $this->factory->makeServerService($provider->type, $provider->api_token);
        $remoteServers = $serverService->getAll();

        $remoteExternalIds = [];

        foreach ($remoteServers as $serverData) {
            $externalId = (string) $serverData->externalId;
            $remoteExternalIds[] = $externalId;

            $provider->servers()->updateOrCreate(
                ['external_id' => $externalId],
                [
                    'organization_id' => $provider->organization_id,
                    'infrastructure_id' => $infrastructure->id,
                    'name' => $serverData->name,
                    'status' => $serverData->status,
                    'type' => $serverData->type,
                    'region' => $serverData->region,
                    'ipv4' => $serverData->ipv4,
                    'ipv6' => $serverData->ipv6,
                ],
            );
        }

        ($this->serverQuery)()
            ->byProvider($provider)
            ->builder()
            ->whereNotIn('external_id', $remoteExternalIds)
            ->delete();
    }
}
