<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\CloudProviderClient;
use App\Data\CloudProviderData;
use App\Enums\CloudProviderType;
use App\Exceptions\LarakubeApiException;

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

    public function handleCommand(CloudProviderClient $cloudProviderClient): int
    {
        try {
            $providers = $cloudProviderClient->list();
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($providers === []) {
            $this->components->info('No cloud providers configured. Run [cloud-provider:add] to add one.');

            return self::SUCCESS;
        }

        $this->table(
            ['Name', 'Type', 'Verified'],
            array_map(fn (CloudProviderData $provider): array => [
                $provider->name,
                CloudProviderType::from($provider->type)->label(),
                $provider->isVerified ? 'Yes' : 'No',
            ], $providers),
        );

        return self::SUCCESS;
    }
}
