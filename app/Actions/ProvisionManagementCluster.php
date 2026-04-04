<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\BootstrapClusterService;
use App\Contracts\CapiInstallerService;
use App\Contracts\KubeconfigReaderService;
use App\Contracts\ManagementClusterClient;
use App\Data\CreateManagementClusterData;
use App\Data\ManagementClusterData;
use App\Data\ProvisionManagementClusterData;
use RuntimeException;

final readonly class ProvisionManagementCluster
{
    public function __construct(
        private ManagementClusterClient $managementClusterClient,
        private ValidatePrerequisites $validatePrerequisites,
        private BootstrapClusterService $bootstrapClusterService,
        private CapiInstallerService $capiInstallerService,
        private KubeconfigReaderService $kubeconfigReaderService,
        private WriteKubeconfigToTempFile $writeKubeconfigToTempFile,
        private CleanupTempKubeconfig $cleanupTempKubeconfig,
    ) {}

    public function handle(ProvisionManagementClusterData $data): ManagementClusterData
    {
        $existing = $this->managementClusterClient->findByProviderAndRegion($data->provider, $data->region);

        if ($existing && ! $data->force) {
            throw new RuntimeException("Management cluster for provider [{$data->provider}] in region [{$data->region}] already exists.");
        }

        if ($existing) {
            $this->bootstrapClusterService->destroy($existing->name);
            $this->managementClusterClient->delete($existing->id);
        }

        $result = $this->validatePrerequisites->handle();

        if (! $result->ok) {
            throw new RuntimeException('Missing prerequisites: '.implode(', ', $result->missing));
        }

        $clusterName = "kuven-mgmt-{$data->region}";

        $cluster = $this->managementClusterClient->create(new CreateManagementClusterData(
            name: $clusterName,
            provider: $data->provider,
            region: $data->region,
            kubernetesVersion: $data->kubernetesVersion,
        ));

        $this->bootstrapClusterService->create($clusterName);

        $kubeconfigPath = $this->writeKubeconfigToTempFile->handle($clusterName);

        $this->capiInstallerService->init($data->provider, $kubeconfigPath);

        $this->cleanupTempKubeconfig->handle($kubeconfigPath);

        $kubeconfig = $this->kubeconfigReaderService->read($clusterName);
        $this->managementClusterClient->storeKubeconfig($cluster->id, $kubeconfig);
        $this->managementClusterClient->markReady($cluster->id);

        return new ManagementClusterData(
            id: $cluster->id,
            name: $cluster->name,
            provider: $cluster->provider,
            region: $cluster->region,
            status: 'ready',
            kubernetesVersion: $data->kubernetesVersion,
        );
    }
}
