<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\ServerManagerInterface;
use App\Data\CreateServerData;
use App\Models\CloudProvider;
use App\Models\Server;

final readonly class CreateServer
{
    public function __construct(private ServerManagerInterface $serverManager) {}

    public function handle(CloudProvider $provider, CreateServerData $data): Server
    {
        $serverData = $this->serverManager->create($provider, $data);

        return $provider->servers()->create([
            'organization_id' => $provider->organization_id,
            'infrastructure_id' => $data->infrastructure_id,
            'external_id' => (string) $serverData->externalId,
            'name' => $serverData->name,
            'status' => $serverData->status,
            'type' => $serverData->type,
            'region' => $serverData->region,
            'ipv4' => $serverData->ipv4,
            'ipv6' => $serverData->ipv6,
        ]);
    }
}
