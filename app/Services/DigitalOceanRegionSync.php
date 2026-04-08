<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RegionSyncService;
use App\Data\RegionData;
use App\Models\Provider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final readonly class DigitalOceanRegionSync implements RegionSyncService
{
    /**
     * @return array<int, RegionData>
     *
     * @throws ConnectionException
     */
    public function fetchRegions(Provider $provider): array
    {
        $response = Http::withToken($provider->api_token)
            ->get('https://api.digitalocean.com/v2/regions');

        /** @var array<int, array{slug: string, name: string, available: bool}> $regions */
        $regions = $response->json('regions');

        return array_map(
            fn (array $region) => new RegionData(
                slug: $region['slug'],
                name: $region['name'],
                country: '',
                city: '',
                metadata: $region,
            ),
            $regions,
        );
    }
}
