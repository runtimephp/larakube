<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RegionSyncService;
use App\Data\RegionData;
use App\Models\Provider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final readonly class HetznerRegionSync implements RegionSyncService
{
    /**
     * @return array<int, RegionData>
     *
     * @throws ConnectionException
     */
    public function fetchRegions(Provider $provider): array
    {
        $response = Http::withToken($provider->api_token)
            ->get('https://api.hetzner.cloud/v1/locations');

        /** @var array<int, array{name: string, description: string, country: string, city: string}> $locations */
        $locations = $response->json('locations');

        return array_map(
            fn (array $location) => new RegionData(
                slug: $location['name'],
                name: $location['description'],
                country: $location['country'],
                city: $location['city'],
                metadata: $location,
            ),
            $locations,
        );
    }
}
