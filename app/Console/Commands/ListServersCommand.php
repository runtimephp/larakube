<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\SyncServers;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\Server;

use function Laravel\Prompts\select;

final class ListServersCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'server:list';

    /**
     * @var string
     */
    protected $description = 'List servers for a cloud provider';

    protected bool $requiresOrganization = true;

    public function handleCommand(SyncServers $syncServers): int
    {
        $organization = Organization::query()->find($this->organization->id);
        $providers = $organization->cloudProviders;

        if ($providers->isEmpty()) {
            $this->components->info('No cloud providers configured. Run [cloud-provider:add] first.');

            return self::SUCCESS;
        }

        $choices = $providers->mapWithKeys(fn (CloudProvider $provider) => [
            $provider->id => "{$provider->name} ({$provider->type->label()})",
        ])->toArray();

        $providerId = select(
            label: 'Select a cloud provider',
            options: $choices,
        );

        $provider = $providers->firstWhere('id', $providerId);

        $this->components->info('Syncing servers...');
        $syncServers->handle($provider);

        $servers = $provider->servers()->get();

        if ($servers->isEmpty()) {
            $this->components->info('No servers found.');

            return self::SUCCESS;
        }

        $this->table(
            ['Name', 'Status', 'Type', 'Region', 'IPv4'],
            $servers->map(fn (Server $server) => [
                $server->name,
                $server->status->label(),
                $server->type,
                $server->region,
                $server->ipv4 ?? '-',
            ]),
        );

        return self::SUCCESS;
    }
}
