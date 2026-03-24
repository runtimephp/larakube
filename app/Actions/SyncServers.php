<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\ServerManagerInterface;
use App\Models\CloudProvider;
use App\Models\Infrastructure;

final readonly class SyncServers
{
    public function __construct(private ServerManagerInterface $serverManager) {}

    public function handle(CloudProvider $provider): void
    {
        $infrastructure = $provider->infrastructures()->first();

        if (! $infrastructure) {
            $infrastructure = Infrastructure::create([
                'organization_id' => $provider->organization_id,
                'cloud_provider_id' => $provider->id,
                'name' => 'Default Infrastructure',
                'description' => 'Auto-created infrastructure for synced servers',
            ]);
        }

        $remoteServers = $this->serverManager->list($provider);

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

        $provider->servers()
            ->whereNotIn('external_id', $remoteExternalIds)
            ->delete();
    }
}
