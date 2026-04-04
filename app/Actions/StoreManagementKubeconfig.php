<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ManagementCluster;
use SensitiveParameter;

final class StoreManagementKubeconfig
{
    public function handle(ManagementCluster $cluster, #[SensitiveParameter] string $kubeconfig): void
    {
        $cluster->forceFill(['kubeconfig' => $kubeconfig])->save();
    }
}
