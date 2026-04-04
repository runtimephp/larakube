<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\BootstrapClusterService;
use App\Contracts\CapiInstallerService;
use App\Contracts\KubeconfigReaderService;
use App\Contracts\ManagementClusterClient;
use App\Data\ApiErrorData;
use App\Data\CreateManagementClusterData;
use App\Data\ManagementClusterData;
use App\Data\ProvisionManagementClusterData;
use App\Enums\ApiErrorCode;
use App\Exceptions\LarakubeApiException;
use RuntimeException;

final readonly class ProvisionManagementCluster
{
    public function __construct(
        private ManagementClusterClient $managementClusterClient,
        private ValidatePrerequisites $validatePrerequisites,
        private BootstrapClusterService $bootstrapClusterService,
        private CapiInstallerService $capiInstallerService,
        private KubeconfigReaderService $kubeconfigReaderService,
    ) {}

    public function handle(ProvisionManagementClusterData $data): ManagementClusterData
    {
        $existing = $this->managementClusterClient->findByProviderAndRegion($data->provider, $data->region);

        if ($existing && ! $data->force) {
            throw new LarakubeApiException(new ApiErrorData(
                message: "Management cluster for provider [{$data->provider}] in region [{$data->region}] already exists.",
                code: ApiErrorCode::ValidationFailed,
            ));
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
        ));

        $this->bootstrapClusterService->create($clusterName);

        $kubeconfig = $this->kubeconfigReaderService->read($clusterName);

        $kubeconfigPath = sys_get_temp_dir()."/{$clusterName}-kubeconfig";
        file_put_contents($kubeconfigPath, $kubeconfig);

        $this->capiInstallerService->init($data->provider, $kubeconfigPath);

        @unlink($kubeconfigPath);

        $this->managementClusterClient->storeKubeconfig($cluster->id, $kubeconfig);
        $this->managementClusterClient->markReady($cluster->id);

        return new ManagementClusterData(
            id: $cluster->id,
            name: $cluster->name,
            provider: $cluster->provider,
            region: $cluster->region,
            status: 'ready',
        );
    }
}
