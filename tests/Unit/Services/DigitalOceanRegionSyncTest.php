<?php

declare(strict_types=1);

use App\Data\RegionData;
use App\Enums\ProviderSlug;
use App\Models\Provider;
use App\Services\DigitalOceanRegionSync;
use Illuminate\Support\Facades\Http;

test('it returns region data from digitalocean api', function (): void {
    Http::fake([
        'api.digitalocean.com/v2/regions' => Http::response([
            'regions' => [
                [
                    'slug' => 'nyc1',
                    'name' => 'New York 1',
                    'available' => true,
                ],
                [
                    'slug' => 'ams3',
                    'name' => 'Amsterdam 3',
                    'available' => true,
                ],
            ],
        ]),
    ]);

    /** @var Provider $provider */
    $provider = Provider::factory()->withApiToken()->create([
        'slug' => ProviderSlug::DigitalOcean,
    ]);

    $service = new DigitalOceanRegionSync();
    $regions = $service->fetchRegions($provider);

    expect($regions)->toHaveCount(2)
        ->and($regions[0])->toBeInstanceOf(RegionData::class)
        ->and($regions[0]->slug)->toBe('nyc1')
        ->and($regions[0]->name)->toBe('New York 1')
        ->and($regions[0]->metadata['available'])->toBeTrue()
        ->and($regions[1]->slug)->toBe('ams3')
        ->and($regions[1]->name)->toBe('Amsterdam 3');
});
