<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\CloudProviderClient;
use App\Contracts\ServerClient;
use App\Data\CreateServerData;
use App\Enums\CloudProviderType;
use App\Exceptions\LarakubeApiException;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

final class CreateServerCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'server:create';

    /**
     * @var string
     */
    protected $description = 'Create a new server on a cloud provider';

    protected bool $requiresOrganization = true;

    protected bool $requiresInfrastructure = true;

    public function handleCommand(ServerClient $serverClient, CloudProviderClient $cloudProviderClient): int
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

        $choices = [];
        foreach ($providers as $p) {
            $choices[$p->id] = "{$p->name} (".CloudProviderType::from($p->type)->label().')';
        }

        $providerId = select(
            label: 'Select a cloud provider',
            options: $choices,
        );

        $selectedProvider = null;
        foreach ($providers as $p) {
            if ($p->id === $providerId) {
                $selectedProvider = $p;
                break;
            }
        }

        $providerType = CloudProviderType::from($selectedProvider->type);
        $name = text(label: 'Server name', required: true);

        if ($providerType === CloudProviderType::Multipass) {
            $image = text(label: 'Ubuntu release', default: 'noble', required: true);
            $cpus = (int) text(label: 'CPUs', default: '1', required: true);
            $memory = text(label: 'Memory', default: '1G', required: true);
            $disk = text(label: 'Disk', default: '5G', required: true);
            $type = 'custom';
            $region = 'local';
        } else {
            $type = text(label: 'Server type', placeholder: 'e.g. cx11 (Hetzner) or s-1vcpu-1gb (DigitalOcean)', required: true);
            $image = text(label: 'Image', default: 'ubuntu-22.04', required: true);
            $region = text(label: 'Region', placeholder: 'e.g. fsn1 (Hetzner) or nyc1 (DigitalOcean)', required: true);
            $cpus = null;
            $memory = null;
            $disk = null;
        }

        $this->components->info('Creating server...');

        try {
            $server = $serverClient->create(
                new CreateServerData(
                    name: $name,
                    type: $type,
                    image: $image,
                    region: $region,
                    infrastructure_id: $this->infrastructure->id,
                    cpus: $cpus,
                    memory: $memory,
                    disk: $disk,
                ),
                $providerId,
            );
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Server [{$server->name}] created successfully.");

        return self::SUCCESS;
    }
}
