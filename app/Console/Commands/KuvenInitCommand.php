<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\ProvisionManagementCluster;
use App\Data\ProvisionManagementClusterData;
use App\Exceptions\LarakubeApiException;
use RuntimeException;

final class KuvenInitCommand extends AuthenticatedCommand
{
    /** @var string */
    protected $signature = 'kuven:init
        {--provider= : Infrastructure provider (docker, hetzner)}
        {--region=local : Region for the management cluster}
        {--kubernetes-version=1.35.3 : Kubernetes version for the management cluster}
        {--force : Re-bootstrap even if a management cluster already exists}';

    /** @var string */
    protected $description = 'Bootstrap a CAPI management cluster';

    public function handleCommand(ProvisionManagementCluster $provisionManagementCluster): int
    {
        $provider = $this->option('provider');

        if (! is_string($provider) || $provider === '') {
            $this->components->error('The --provider option is required (e.g. --provider=docker).');

            return self::FAILURE;
        }

        /** @var string $region */
        $region = $this->option('region');

        try {
            /** @var string $kubernetesVersion */
            $kubernetesVersion = $this->option('kubernetes-version');

            $cluster = $provisionManagementCluster->handle(new ProvisionManagementClusterData(
                providerId: $provider,
                platformRegionId: $region,
                version: $kubernetesVersion,
                force: (bool) $this->option('force'),
            ));

            $this->components->info("Management cluster [{$cluster->name}] is ready.");

            return self::SUCCESS;
        } catch (LarakubeApiException|RuntimeException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
