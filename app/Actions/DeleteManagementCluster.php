<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\BootstrapClusterService;
use App\Models\ManagementCluster;

final readonly class DeleteManagementCluster
{
    public function __construct(
        private BootstrapClusterService $bootstrapService,
    ) {}

    public function handle(ManagementCluster $cluster): void
    {
        if ($this->bootstrapService->exists($cluster->name)) {
            $this->bootstrapService->destroy($cluster->name);
        }

        $cluster->delete();
    }
}
