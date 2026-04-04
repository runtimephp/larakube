<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CreateClusterManifestData;

interface ClusterManifestGenerator
{
    /**
     * @return list<array<string, mixed>>
     */
    public function generate(CreateClusterManifestData $createClusterManifestData): array;
}
