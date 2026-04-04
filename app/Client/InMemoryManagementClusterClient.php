<?php

declare(strict_types=1);

namespace App\Client;

use App\Contracts\ManagementClusterClient;
use App\Data\ApiErrorData;
use App\Data\CreateManagementClusterData;
use App\Data\ManagementClusterData;
use App\Enums\ApiErrorCode;
use App\Enums\ManagementClusterStatus;
use App\Exceptions\LarakubeApiException;
use Illuminate\Support\Str;

final class InMemoryManagementClusterClient implements ManagementClusterClient
{
    /** @var array<string, ManagementClusterData> */
    private array $clusters = [];

    /** @var array<string, string> */
    private array $kubeconfigs = [];

    public function create(CreateManagementClusterData $data): ManagementClusterData
    {
        $cluster = new ManagementClusterData(
            id: Str::uuid()->toString(),
            name: $data->name,
            provider: $data->provider,
            region: $data->region,
            status: ManagementClusterStatus::Bootstrapping->value,
            kubernetesVersion: $data->kubernetesVersion,
        );

        $this->clusters[$cluster->id] = $cluster;

        return $cluster;
    }

    public function findByProviderAndRegion(string $provider, string $region): ?ManagementClusterData
    {
        foreach ($this->clusters as $cluster) {
            if ($cluster->provider === $provider && $cluster->region === $region) {
                return $cluster;
            }
        }

        return null;
    }

    public function storeKubeconfig(string $id, string $kubeconfig): void
    {
        $this->requireCluster($id);
        $this->kubeconfigs[$id] = $kubeconfig;
    }

    public function markReady(string $id): void
    {
        $existing = $this->requireCluster($id);

        $this->clusters[$id] = new ManagementClusterData(
            id: $existing->id,
            name: $existing->name,
            provider: $existing->provider,
            region: $existing->region,
            status: ManagementClusterStatus::Ready->value,
            kubernetesVersion: $existing->kubernetesVersion,
        );
    }

    public function delete(string $id): void
    {
        $this->requireCluster($id);
        unset($this->clusters[$id], $this->kubeconfigs[$id]);
    }

    public function getKubeconfig(string $id): ?string
    {
        return $this->kubeconfigs[$id] ?? null;
    }

    private function requireCluster(string $id): ManagementClusterData
    {
        return $this->clusters[$id] ?? throw new LarakubeApiException(new ApiErrorData(
            message: 'Management cluster not found.',
            code: ApiErrorCode::NotFound,
        ));
    }
}
