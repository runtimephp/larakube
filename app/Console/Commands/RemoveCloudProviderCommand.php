<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Organization;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

final class RemoveCloudProviderCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'cloud-provider:remove';

    /**
     * @var string
     */
    protected $description = 'Remove a cloud provider from the current organization';

    protected bool $requiresOrganization = true;

    public function handleCommand(): int
    {
        $organization = Organization::query()->find($this->organization->id);
        $providers = $organization->cloudProviders;

        if ($providers->isEmpty()) {
            $this->components->info('No cloud providers to remove.');

            return self::SUCCESS;
        }

        $choices = $providers->mapWithKeys(fn ($provider) => [
            $provider->id => "{$provider->name} ({$provider->type->label()})",
        ])->toArray();

        $selectedId = select(
            label: 'Select a cloud provider to remove',
            options: $choices,
        );

        $selected = $providers->firstWhere('id', $selectedId);

        if (! confirm("Are you sure you want to remove [{$selected->name}]?")) {
            $this->components->info('Cancelled.');

            return self::SUCCESS;
        }

        $selected->delete();

        $this->components->info("Cloud provider [{$selected->name}] removed.");

        return self::SUCCESS;
    }
}
