<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Services\SessionManager;
use App\Data\SessionInfrastructureData;
use App\Models\CloudProvider;
use App\Models\Organization;

use function Laravel\Prompts\select;

final class SelectInfrastructureCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'infrastructure:select';

    /**
     * @var string
     */
    protected $description = 'Select an infrastructure to work with';

    protected bool $requiresOrganization = true;

    public function handleCommand(SessionManager $session): int
    {
        $organization = Organization::query()->find($this->organization->id);
        $providers = $organization->cloudProviders;

        if ($providers->isEmpty()) {
            $this->components->error('No cloud providers configured. Run [cloud-provider:add] first.');

            return self::FAILURE;
        }

        $choices = $providers->mapWithKeys(fn (CloudProvider $provider) => [
            $provider->id => "{$provider->name} ({$provider->type->label()})",
        ])->toArray();

        $providerId = select(
            label: 'Select a cloud provider',
            options: $choices,
        );

        $provider = $providers->firstWhere('id', $providerId);
        $infrastructures = $provider->infrastructures;

        if ($infrastructures->isEmpty()) {
            $this->components->error('No infrastructures configured. Run [infrastructure:create] first.');

            return self::FAILURE;
        }

        $infraChoices = $infrastructures->pluck('name', 'id')->toArray();

        $selectedId = select(
            label: 'Select an infrastructure',
            options: $infraChoices,
        );

        $selected = $infrastructures->firstWhere('id', $selectedId);

        $session->setInfrastructure(new SessionInfrastructureData(
            id: $selected->id,
            name: $selected->name,
        ));

        $this->components->info("Selected infrastructure [{$selected->name}].");

        return self::SUCCESS;
    }
}
