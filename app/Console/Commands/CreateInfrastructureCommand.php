<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\CloudProviderClient;
use App\Contracts\InfrastructureClient;
use App\Data\CreateInfrastructureData;
use App\Enums\CloudProviderType;
use App\Exceptions\LarakubeApiException;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

final class CreateInfrastructureCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'infrastructure:create {--provider= : Cloud provider ID} {--name= : Infrastructure name} {--description= : Infrastructure description}';

    /**
     * @var string
     */
    protected $description = 'Create a new infrastructure';

    protected bool $requiresOrganization = true;

    public function handleCommand(InfrastructureClient $infrastructureClient, CloudProviderClient $cloudProviderClient): int
    {
        try {
            $providers = $cloudProviderClient->list();
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($providers === []) {
            $this->components->info('No cloud providers configured. Run [cloud-provider:add] first.');

            return self::SUCCESS;
        }

        $providerOption = $this->option('provider');

        if ($providerOption) {
            $provider = null;
            foreach ($providers as $p) {
                if ($p->id === $providerOption) {
                    $provider = $p;
                    break;
                }
            }
            if ($provider === null) {
                $this->components->error('Provider not found.');

                return self::FAILURE;
            }
        } else {
            $choices = [];
            foreach ($providers as $p) {
                $choices[$p->id] = "{$p->name} (".CloudProviderType::from($p->type)->label().')';
            }

            $providerId = select(
                label: 'Select a cloud provider',
                options: $choices,
            );

            $provider = null;
            foreach ($providers as $p) {
                if ($p->id === $providerId) {
                    $provider = $p;
                    break;
                }
            }
        }

        $nameOption = $this->option('name');
        $name = $nameOption ?: text(
            label: 'Infrastructure name',
            required: true,
        );

        $descOption = $this->option('description');
        $description = $descOption ?: text(
            label: 'Description (optional)',
        );

        try {
            $infrastructure = $infrastructureClient->create(
                new CreateInfrastructureData(
                    name: $name,
                    description: $description ?: null,
                ),
                $provider->id,
            );
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Infrastructure [{$infrastructure->name}] created successfully.");

        return self::SUCCESS;
    }
}
