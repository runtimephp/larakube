<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\CloudProviderClient;
use App\Contracts\ServerClient;
use App\Enums\CloudProviderType;
use App\Exceptions\LarakubeApiException;

use function Laravel\Prompts\select;

final class SyncServerCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'server:sync {--provider= : Cloud provider ID}';

    /**
     * @var string
     */
    protected $description = 'Sync servers from a cloud provider';

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
            $providerId = $provider->id;
        } else {
            $choices = [];
            foreach ($providers as $p) {
                $choices[$p->id] = "{$p->name} (".CloudProviderType::from($p->type)->label().')';
            }

            $providerId = select(
                label: 'Select a cloud provider',
                options: $choices,
            );
        }

        $this->components->info('Syncing servers...');

        try {
            $summary = $serverClient->sync($providerId);
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Sync complete: {$summary->created} created, {$summary->updated} updated, {$summary->deleted} deleted.");

        return self::SUCCESS;
    }
}
