<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CreateBootstrapCluster;
use App\Actions\CreateManagementCluster;
use App\Actions\DeleteManagementCluster;
use App\Actions\InstallCapiControllers;
use App\Actions\MarkManagementClusterReady;
use App\Actions\StoreManagementKubeconfig;
use App\Actions\ValidatePrerequisites;
use App\Actions\WriteKubeconfigToTempFile;
use App\Contracts\KubeconfigReaderService;
use App\Data\CreateManagementClusterData;
use App\Queries\ManagementClusterQuery;
use Illuminate\Console\Command;

final class KuvenInitCommand extends Command
{
    /** @var string */
    protected $signature = 'kuven:init
        {--provider= : Infrastructure provider (docker, hetzner)}
        {--region=local : Region for the management cluster}
        {--force : Re-bootstrap even if a management cluster already exists}';

    /** @var string */
    protected $description = 'Bootstrap a CAPI management cluster';

    public function handle(
        ManagementClusterQuery $managementClusterQuery,
        ValidatePrerequisites $validatePrerequisites,
        CreateManagementCluster $createManagementCluster,
        DeleteManagementCluster $deleteManagementCluster,
        CreateBootstrapCluster $createBootstrapCluster,
        InstallCapiControllers $installCapiControllers,
        WriteKubeconfigToTempFile $writeKubeconfigToTempFile,
        KubeconfigReaderService $kubeconfigReaderService,
        StoreManagementKubeconfig $storeManagementKubeconfig,
        MarkManagementClusterReady $markManagementClusterReady,
    ): int {
        $provider = $this->option('provider');

        if (! is_string($provider) || $provider === '') {
            $this->components->error('The --provider option is required (e.g. --provider=docker).');

            return self::FAILURE;
        }

        /** @var string $region */
        $region = $this->option('region');

        $force = (bool) $this->option('force');

        $existing = ($managementClusterQuery)()->byProvider($provider)->byRegion($region)->first();

        if ($existing && ! $force) {
            $this->components->error("Management cluster for provider [{$provider}] in region [{$region}] already exists. Use --force to re-bootstrap.");

            return self::FAILURE;
        }

        if ($existing) {
            $deleteManagementCluster->handle($existing);
        }

        $result = $validatePrerequisites->handle();

        if (! $result->ok) {
            $this->components->error('Missing prerequisites: '.implode(', ', $result->missing));

            return self::FAILURE;
        }

        $cluster = $createManagementCluster->handle(new CreateManagementClusterData(
            name: "kuven-mgmt-{$region}",
            provider: $provider,
            region: $region,
        ));

        $this->components->info('Creating bootstrap cluster...');
        $createBootstrapCluster->handle($cluster->name);

        $this->components->info('Writing kubeconfig...');
        $kubeconfigPath = $writeKubeconfigToTempFile->handle($cluster->name);

        $this->components->info('Installing CAPI controllers...');
        $installCapiControllers->handle($provider, $kubeconfigPath);

        $this->components->info('Storing management cluster kubeconfig...');
        $kubeconfig = $kubeconfigReaderService->read($cluster->name);
        $storeManagementKubeconfig->handle($cluster, $kubeconfig);

        @unlink($kubeconfigPath);

        $markManagementClusterReady->handle($cluster);

        $this->components->info("Management cluster [{$cluster->name}] is ready.");

        return self::SUCCESS;
    }
}
