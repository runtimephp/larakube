<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ProvisionManagementClusterData
{
    public function __construct(
        public string $provider,
        public string $region,
        public bool $force = false,
    ) {}
}
