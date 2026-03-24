<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CreateServer;
use App\Data\CreateServerData;
use App\Models\CloudProvider;
use App\Models\Organization;
use Throwable;

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

    public function handleCommand(CreateServer $createServer): int
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

        $type = text(
            label: 'Server type',
            placeholder: 'e.g. cx11 (Hetzner) or s-1vcpu-1gb (DigitalOcean)',
            required: true,
        );

        $image = text(
            label: 'Image',
            default: 'ubuntu-22.04',
            required: true,
        );

        $region = text(
            label: 'Region',
            placeholder: 'e.g. fsn1 (Hetzner) or nyc1 (DigitalOcean)',
            required: true,
        );

        $this->components->info('Creating server...');

        try {
            $server = $createServer->handle(
                $provider,
                new CreateServerData(
                    name: $name,
                    type: $type,
                    image: $image,
                    region: $region,
                ),
            );
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Server [{$server->name}] created successfully.");

        return self::SUCCESS;
    }
}
