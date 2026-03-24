<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\DeleteServer;
use App\Actions\SyncServers;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\Server;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

final class DeleteServerCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'server:delete';

    /**
     * @var string
     */
    protected $description = 'Delete a server from a cloud provider';

    protected bool $requiresOrganization = true;

    public function handleCommand(SyncServers $syncServers, DeleteServer $deleteServer): int
    {
        $organization = Organization::query()->find($this->organization->id);
        $providers = $organization->cloudProviders;

        if ($providers->isEmpty()) {
            $this->components->info('No cloud providers configured. Run [cloud-provider:add] first.');

            return self::SUCCESS;
        }

        $providerChoices = $providers->mapWithKeys(fn (CloudProvider $provider) => [
            $provider->id => "{$provider->name} ({$provider->type->label()})",
        ])->toArray();

        $providerId = select(
            label: 'Select a cloud provider',
            options: $providerChoices,
        );

        $provider = $providers->firstWhere('id', $providerId);

        $this->components->info('Syncing servers...');
        $syncServers->handle($provider);

        $servers = $provider->servers()->get();

        if ($servers->isEmpty()) {
            $this->components->info('No servers to delete.');

            return self::SUCCESS;
        }

        $serverChoices = $servers->mapWithKeys(fn (Server $server) => [
            $server->id => "{$server->name} ({$server->status->label()})",
        ])->toArray();

        $serverId = select(
            label: 'Select a server to delete',
            options: $serverChoices,
        );

        $server = $servers->firstWhere('id', $serverId);

        if (! confirm("Are you sure you want to delete [{$server->name}]?")) {
            $this->components->info('Cancelled.');

            return self::SUCCESS;
        }

        try {
            $deleteServer->handle($server);
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Server [{$server->name}] deleted.");

        return self::SUCCESS;
    }
}
