<?php

declare(strict_types=1);

use App\Data\RegionData;
use App\Enums\ProviderSlug;
use App\Models\Provider;
use App\Services\HetznerRegionSync;
use Illuminate\Support\Facades\Http;

function fakeHetznerLocationsResponse(): void
{
    Http::fake([
        'api.hetzner.cloud/*' => Http::response([
            'locations' => [
                [
                    'id' => 1,
                    'name' => 'fsn1',
                    'description' => 'Falkenstein DC Park 1',
                    'country' => 'DE',
                    'city' => 'Falkenstein',
                    'latitude' => 50.47612,
                    'longitude' => 12.370071,
                    'network_zone' => 'eu-central',
                ],
                [
                    'id' => 2,
                    'name' => 'nbg1',
                    'description' => 'Nuremberg DC Park 1',
                    'country' => 'DE',
                    'city' => 'Nuremberg',
                    'latitude' => 49.452102,
                    'longitude' => 11.076665,
                    'network_zone' => 'eu-central',
                ],
            ],
        ]),
    ]);
}

test('it returns region data from hetzner api', function (): void {
    fakeHetznerLocationsResponse();

    /** @var Provider $provider */
    $provider = Provider::factory()->withApiToken()->create([
        'slug' => ProviderSlug::Hetzner,
    ]);

    $service = new HetznerRegionSync();
    $regions = $service->fetchRegions($provider);

    expect($regions)->toHaveCount(2)
        ->and($regions[0])->toBeInstanceOf(RegionData::class)
        ->and($regions[0]->slug)->toBe('fsn1')
        ->and($regions[0]->name)->toBe('Falkenstein DC Park 1')
        ->and($regions[0]->country)->toBe('DE')
        ->and($regions[0]->city)->toBe('Falkenstein')
        ->and($regions[0]->metadata['network_zone'])->toBe('eu-central')
        ->and($regions[1]->slug)->toBe('nbg1')
        ->and($regions[1]->name)->toBe('Nuremberg DC Park 1');
});
