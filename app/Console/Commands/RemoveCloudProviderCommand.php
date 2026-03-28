<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\CloudProviderClient;
use App\Enums\CloudProviderType;
use App\Exceptions\LarakubeApiException;

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

    public function handleCommand(CloudProviderClient $cloudProviderClient): int
    {
        try {
            $providers = $cloudProviderClient->list();
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($providers === []) {
            $this->components->info('No cloud providers to remove.');

            return self::SUCCESS;
        }

        $choices = [];
        foreach ($providers as $provider) {
            $choices[$provider->id] = "{$provider->name} (".CloudProviderType::from($provider->type)->label().')';
        }

        $selectedId = select(
            label: 'Select a cloud provider to remove',
            options: $choices,
        );

        $selected = null;
        foreach ($providers as $provider) {
            if ($provider->id === $selectedId) {
                $selected = $provider;
                break;
            }
        }

        if (! confirm("Are you sure you want to remove [{$selected->name}]?")) {
            $this->components->info('Cancelled.');

            return self::SUCCESS;
        }

        try {
            $cloudProviderClient->delete($selected->id);
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Cloud provider [{$selected->name}] removed.");

        return self::SUCCESS;
    }
}
