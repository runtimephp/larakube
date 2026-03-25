<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CreateInfrastructure;
use App\Data\CreateInfrastructureData;
use App\Models\CloudProvider;
use App\Models\Organization;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

final class CreateInfrastructureCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'infrastructure:create';

    /**
     * @var string
     */
    protected $description = 'Create a new infrastructure';

    protected bool $requiresOrganization = true;

    public function handleCommand(CreateInfrastructure $createInfrastructure): int
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
            label: 'Infrastructure name',
            required: true,
        );

        $description = text(
            label: 'Description (optional)',
        );

        $infrastructure = $createInfrastructure->handle(
            $provider,
            new CreateInfrastructureData(
                name: $name,
                description: $description ?: null,
            ),
        );

        $this->components->info("Infrastructure [{$infrastructure->name}] created successfully.");

        return self::SUCCESS;
    }
}
