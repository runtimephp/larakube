<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ProvisionManagementClusterData
{
    public function __construct(
        public string $providerId,
        public string $platformRegionId,
        public string $version,
        public bool $force = false,
    ) {}
}
