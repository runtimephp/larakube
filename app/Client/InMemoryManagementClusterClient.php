<?php

declare(strict_types=1);

namespace App\Client;

use App\Contracts\ManagementClusterClient;
use App\Data\CreateManagementClusterData;
use App\Data\ManagementClusterData;
use App\Enums\ManagementClusterStatus;
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
        $this->kubeconfigs[$id] = $kubeconfig;
    }

    public function markReady(string $id): void
    {
        $existing = $this->clusters[$id] ?? null;

        if ($existing) {
            $this->clusters[$id] = new ManagementClusterData(
                id: $existing->id,
                name: $existing->name,
                provider: $existing->provider,
                region: $existing->region,
                status: ManagementClusterStatus::Ready->value,
            );
        }
    }

    public function delete(string $id): void
    {
        unset($this->clusters[$id], $this->kubeconfigs[$id]);
    }

    public function getKubeconfig(string $id): ?string
    {
        return $this->kubeconfigs[$id] ?? null;
    }
}
