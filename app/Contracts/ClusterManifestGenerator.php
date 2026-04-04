<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CreateClusterManifestData;
use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;

interface ClusterManifestGenerator
{
    /**
     * @return list<ManifestContract>
     */
    public function generate(CreateClusterManifestData $createClusterManifestData): array;
}
