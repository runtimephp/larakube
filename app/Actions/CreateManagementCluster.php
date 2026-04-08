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
            'provider_id' => $data->providerId,
            'platform_region_id' => $data->platformRegionId,
            'version' => $data->version,
            'status' => ManagementClusterStatus::Bootstrapping,
        ]);
    }
}
