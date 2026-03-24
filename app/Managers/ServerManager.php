<?php

declare(strict_types=1);

namespace App\Managers;

use App\Contracts\ServerManagerInterface;
use App\Contracts\ServiceFactoryInterface;
use App\Data\CreateServerData;
use App\Data\ServerData;
use App\Models\CloudProvider;

final readonly class ServerManager implements ServerManagerInterface
{
    public function __construct(private ServiceFactoryInterface $serviceFactory) {}

    /**
     * @return array<int, ServerData>
     */
    public function list(CloudProvider $provider): array
    {
        return $this->serviceFactory
            ->makeServerService($provider->type)
            ->getServers($provider->api_token);
    }

    public function create(CloudProvider $provider, CreateServerData $data): ServerData
    {
        return $this->serviceFactory
            ->makeServerService($provider->type)
            ->createServer($provider->api_token, $data);
    }

    public function findByName(CloudProvider $provider, string $name): ?ServerData
    {
        return $this->serviceFactory
            ->makeServerService($provider->type)
            ->getServerByName($provider->api_token, $name);
    }

    public function delete(CloudProvider $provider, int|string $externalId): bool
    {
        return $this->serviceFactory
            ->makeServerService($provider->type)
            ->deleteServer($provider->api_token, $externalId);
    }
}
