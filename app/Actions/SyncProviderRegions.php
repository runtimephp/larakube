<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ProviderSlug;
use App\Models\PlatformRegion;
use App\Models\Provider;
use App\Services\DigitalOceanRegionSync;
use App\Services\HetznerRegionSync;
use Illuminate\Http\Client\ConnectionException;
use InvalidArgumentException;

final readonly class SyncProviderRegions
{
    /**
     * @throws ConnectionException
     */
    public function handle(Provider $provider): int
    {
        $service = match ($provider->slug) {
            ProviderSlug::Hetzner => app(HetznerRegionSync::class),
            ProviderSlug::DigitalOcean => app(DigitalOceanRegionSync::class),
            default => throw new InvalidArgumentException(
                "Region sync is not supported for provider: {$provider->slug->value}",
            ),
        };

        $regions = $service->fetchRegions($provider);

        foreach ($regions as $region) {
            PlatformRegion::query()->updateOrCreate(
                [
                    'provider_id' => $provider->id,
                    'slug' => $region->slug,
                ],
                [
                    'name' => $region->name,
                    'country' => $region->country,
                    'city' => $region->city,
                    'is_available' => true,
                    'metadata' => $region->metadata,
                ],
            );
        }

        return count($regions);
    }
}
