<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\ServerManagerInterface;
use App\Models\CloudProvider;
use App\Models\Organization;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

final class ShowServerCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'server:show';

    /**
     * @var string
     */
    protected $description = 'Show details of a server by name';

    protected bool $requiresOrganization = true;

    public function handleCommand(ServerManagerInterface $serverManager): int
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

        $name = text(
            label: 'Server name',
            required: true,
        );

        $serverData = $serverManager->findByName($provider, $name);

        if ($serverData === null) {
            $this->components->error("Server [{$name}] not found.");

            return self::FAILURE;
        }

        $this->table(
            ['Field', 'Value'],
            [
                ['External ID', (string) $serverData->externalId],
                ['Name', $serverData->name],
                ['Status', $serverData->status->label()],
                ['Type', $serverData->type],
                ['Region', $serverData->region],
                ['IPv4', $serverData->ipv4 ?? '-'],
                ['IPv6', $serverData->ipv6 ?? '-'],
            ],
        );

        return self::SUCCESS;
    }
}
