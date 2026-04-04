<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ManagementClusterStatus;
use App\Models\ManagementCluster;

final class MarkManagementClusterReady
{
    public function handle(ManagementCluster $cluster): void
    {
        $cluster->update(['status' => ManagementClusterStatus::Ready]);
    }
}
