<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\ServerManagerInterface;
use App\Models\Server;
use RuntimeException;

final readonly class DeleteServer
{
    public function __construct(private ServerManagerInterface $serverManager) {}

    /**
     * @throws RuntimeException
     */
    public function handle(Server $server): void
    {
        $deleted = $this->serverManager->delete(
            $server->cloudProvider,
            $server->external_id,
        );

        if (! $deleted) {
            throw new RuntimeException("Failed to delete server [{$server->name}] from the provider.");
        }

        $server->delete();
    }
}
