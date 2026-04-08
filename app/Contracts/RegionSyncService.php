<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\RegionData;
use App\Models\Provider;

interface RegionSyncService
{
    /**
     * @return array<int, RegionData>
     */
    public function fetchRegions(Provider $provider): array;
}
