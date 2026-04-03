<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\KubeconfigReaderService;
use App\Models\ManagementCluster;

final readonly class StoreManagementKubeconfig
{
    public function __construct(
        private KubeconfigReaderService $reader,
    ) {}

    public function handle(ManagementCluster $cluster, string $clusterName): void
    {
        $kubeconfig = $this->reader->read($clusterName);

        $cluster->forceFill(['kubeconfig' => $kubeconfig])->save();
    }
}
