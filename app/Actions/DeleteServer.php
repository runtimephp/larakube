<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Server;
use App\Services\CloudProviderFactory;
use RuntimeException;

final readonly class DeleteServer
{
    public function __construct(private CloudProviderFactory $factory) {}

    /**
     * @throws RuntimeException
     */
    public function handle(Server $server): void
    {
        $serverService = $this->factory->makeServerService(
            $server->cloudProvider->type,
            $server->cloudProvider->api_token,
        );

        $deleted = $serverService->destroy($server->external_id);

        if (! $deleted) {
            throw new RuntimeException("Failed to delete server [{$server->name}] from the provider.");
        }

        $server->delete();
    }
}
