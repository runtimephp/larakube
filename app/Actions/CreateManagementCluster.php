<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateManagementClusterData;
use App\Enums\ManagementClusterStatus;
use App\Models\ManagementCluster;

final class CreateManagementCluster
{
    public function handle(CreateManagementClusterData $data): ManagementCluster
    {
        return ManagementCluster::query()->create([
            'name' => $data->name,
            'provider' => $data->provider,
            'region' => $data->region,
            'kubernetes_version' => $data->kubernetesVersion,
            'status' => ManagementClusterStatus::Bootstrapping,
        ]);
    }
}
