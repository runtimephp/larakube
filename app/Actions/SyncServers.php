<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\ServerManagerInterface;
use App\Models\CloudProvider;

final readonly class SyncServers
{
    public function __construct(private ServerManagerInterface $serverManager) {}

    public function handle(CloudProvider $provider): void
    {
        $remoteServers = $this->serverManager->list($provider);

        $remoteExternalIds = [];

        foreach ($remoteServers as $serverData) {
            $externalId = (string) $serverData->externalId;
            $remoteExternalIds[] = $externalId;

            $provider->servers()->updateOrCreate(
                ['external_id' => $externalId],
                [
                    'organization_id' => $provider->organization_id,
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
