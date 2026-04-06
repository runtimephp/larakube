<?php

declare(strict_types=1);

namespace App\Data;

final readonly class CreateClusterManifestData
{
    public function __construct(
        public string $name,
        public string $namespace,
        public string $provider,
        public string $kubernetesVersion,
        public int $controlPlaneCount,
        public int $workerCount,
        public ?string $region = null,
        public ?string $machineType = null,
        public ?string $hcloudToken = null,
    ) {}
}
