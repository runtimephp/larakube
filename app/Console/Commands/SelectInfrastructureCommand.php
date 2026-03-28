<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Services\SessionManager;
use App\Contracts\CloudProviderClient;
use App\Contracts\InfrastructureClient;
use App\Data\InfrastructureData;
use App\Data\SessionInfrastructureData;
use App\Enums\CloudProviderType;
use App\Exceptions\LarakubeApiException;

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

    public function handleCommand(
        SessionManager $session,
        CloudProviderClient $cloudProviderClient,
        InfrastructureClient $infrastructureClient,
    ): int {
        try {
            $providers = $cloudProviderClient->list();
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($providers === []) {
            $this->components->error('No cloud providers configured. Run [cloud-provider:add] first.');

            return self::FAILURE;
        }

        $choices = [];
        foreach ($providers as $provider) {
            $choices[$provider->id] = "{$provider->name} (".CloudProviderType::from($provider->type)->label().')';
        }

        $providerId = select(
            label: 'Select a cloud provider',
            options: $choices,
        );

        try {
            $infrastructures = $infrastructureClient->list();
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        // Filter to selected provider
        $filtered = array_values(array_filter(
            $infrastructures,
            fn (InfrastructureData $infra): bool => $infra->cloudProviderId === $providerId,
        ));

        if ($filtered === []) {
            $this->components->error('No infrastructures configured. Run [infrastructure:create] first.');

            return self::FAILURE;
        }

        $infraChoices = [];
        foreach ($filtered as $infra) {
            $infraChoices[$infra->id] = $infra->name;
        }

        $selectedId = select(
            label: 'Select an infrastructure',
            options: $infraChoices,
        );

        $selected = null;
        foreach ($filtered as $infra) {
            if ($infra->id === $selectedId) {
                $selected = $infra;
                break;
            }
        }

        $session->setInfrastructure(new SessionInfrastructureData(
            id: $selected->id,
            name: $selected->name,
        ));

        $this->components->info("Selected infrastructure [{$selected->name}].");

        return self::SUCCESS;
    }
}
