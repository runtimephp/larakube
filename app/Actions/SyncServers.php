<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\SyncSummaryData;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Queries\ServerQuery;
use App\Services\CloudProviderFactory;

final readonly class SyncServers
{
    public function __construct(
        private CloudProviderFactory $factory,
        private ServerQuery $serverQuery,
    ) {}

    public function handle(CloudProvider $provider, Infrastructure $infrastructure): SyncSummaryData
    {
        $serverService = $this->factory->makeServerService($provider->type, $provider->api_token);
        $remoteServers = $serverService->getAll();

        $remoteExternalIds = [];
        $created = 0;
        $updated = 0;

        foreach ($remoteServers as $serverData) {
            $externalId = (string) $serverData->externalId;
            $remoteExternalIds[] = $externalId;

            $existing = ($this->serverQuery)()
                ->byProvider($provider)
                ->byExternalId($externalId)
                ->first();

            if ($existing !== null) {
                $existing->update([
                    'name' => $serverData->name,
                    'status' => $serverData->status,
                    'type' => $serverData->type,
                    'region' => $serverData->region,
                    'ipv4' => $serverData->ipv4,
                    'ipv6' => $serverData->ipv6,
                ]);
                $updated++;
            } else {
                $provider->servers()->create([
                    'organization_id' => $provider->organization_id,
                    'infrastructure_id' => $infrastructure->id,
                    'external_id' => $externalId,
                    'name' => $serverData->name,
                    'status' => $serverData->status,
                    'type' => $serverData->type,
                    'region' => $serverData->region,
                    'ipv4' => $serverData->ipv4,
                    'ipv6' => $serverData->ipv6,
                ]);
                $created++;
            }
        }

        $deleted = ($this->serverQuery)()
            ->byProvider($provider)
            ->builder()
            ->whereNotIn('external_id', $remoteExternalIds)
            ->delete();

        return new SyncSummaryData(
            created: $created,
            updated: $updated,
            deleted: $deleted,
        );
    }
}
