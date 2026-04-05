<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\InfrastructureStatus;
use App\Models\KubernetesCluster;

final class MarkClusterFailed
{
    public function handle(KubernetesCluster $cluster): void
    {
        $cluster->update(['status' => InfrastructureStatus::Failed]);
    }
}
