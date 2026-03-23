<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Organization;

final class ListCloudProvidersCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'cloud-provider:list';

    /**
     * @var string
     */
    protected $description = 'List cloud providers for the current organization';

    protected bool $requiresOrganization = true;

    public function handleCommand(): int
    {
        /** @var Organization|null $organization */
        $organization = Organization::query()->find($this->organization->id);
        $providers = $organization->cloudProviders;

        if ($providers->isEmpty()) {
            $this->components->info('No cloud providers configured. Run [cloud-provider:add] to add one.');

            return self::SUCCESS;
        }

        $this->table(
            ['Name', 'Type', 'Verified', 'Created'],
            $providers->map(fn ($provider) => [
                $provider->name,
                $provider->type->label(),
                $provider->is_verified ? 'Yes' : 'No',
                $provider->created_at->format('Y-m-d H:i'),
            ]),
        );

        return self::SUCCESS;
    }
}
