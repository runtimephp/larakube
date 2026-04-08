<?php

declare(strict_types=1);

use App\Actions\SyncProviderRegions;
use App\Enums\ProviderSlug;
use App\Models\PlatformRegion;
use App\Models\Provider;
use Illuminate\Support\Facades\Http;

function fakeHetznerLocations(): void
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

test('it syncs regions for a hetzner provider', function (): void {
    fakeHetznerLocations();

    /** @var Provider $provider */
    $provider = Provider::factory()->withApiToken()->create([
        'slug' => ProviderSlug::Hetzner,
    ]);

    $action = new SyncProviderRegions();
    $count = $action->handle($provider);

    expect($count)->toBe(2)
        ->and(PlatformRegion::query()->where('provider_id', $provider->id)->count())->toBe(2);

    /** @var PlatformRegion $fsn1 */
    $fsn1 = PlatformRegion::query()->where('slug', 'fsn1')->sole();

    expect($fsn1->provider_id)->toBe($provider->id)
        ->and($fsn1->name)->toBe('Falkenstein DC Park 1')
        ->and($fsn1->country)->toBe('DE')
        ->and($fsn1->city)->toBe('Falkenstein')
        ->and($fsn1->is_available)->toBeTrue()
        ->and($fsn1->metadata)->toBeArray()
        ->and($fsn1->metadata['network_zone'])->toBe('eu-central');
});

test('it is idempotent when run twice', function (): void {
    fakeHetznerLocations();

    /** @var Provider $provider */
    $provider = Provider::factory()->withApiToken()->create([
        'slug' => ProviderSlug::Hetzner,
    ]);

    $action = new SyncProviderRegions();
    $action->handle($provider);

    fakeHetznerLocations();

    $action->handle($provider);

    expect(PlatformRegion::query()->where('provider_id', $provider->id)->count())->toBe(2);
});

test('it updates existing regions on re-sync', function (): void {
    /** @var Provider $provider */
    $provider = Provider::factory()->withApiToken()->create([
        'slug' => ProviderSlug::Hetzner,
    ]);

    PlatformRegion::factory()->create([
        'provider_id' => $provider->id,
        'slug' => 'fsn1',
        'name' => 'Old Name',
    ]);

    fakeHetznerLocations();

    $action = new SyncProviderRegions();
    $action->handle($provider);

    /** @var PlatformRegion $fsn1 */
    $fsn1 = PlatformRegion::query()->where('slug', 'fsn1')->sole();

    expect($fsn1->name)->toBe('Falkenstein DC Park 1');
});

test('it throws for unsupported provider slug', function (): void {
    /** @var Provider $provider */
    $provider = Provider::factory()->create([
        'slug' => ProviderSlug::Docker,
    ]);

    $action = new SyncProviderRegions();
    $action->handle($provider);
})->throws(InvalidArgumentException::class, 'Region sync is not supported for provider: docker');
