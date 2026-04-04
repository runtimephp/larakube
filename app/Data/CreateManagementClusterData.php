<?php

declare(strict_types=1);

namespace App\Data;

final readonly class CreateManagementClusterData
{
    public function __construct(
        public string $name,
        public string $provider,
        public string $region,
        public string $kubernetesVersion,
    ) {}
}
