<?php

declare(strict_types=1);

use App\Enums\PlatformRole;
use App\Enums\ProviderSlug;
use App\Models\PlatformRegion;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('a platform administrator can trigger region sync', function () {
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

    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var Provider $provider */
    $provider = Provider::factory()->withApiToken()->create([
        'slug' => ProviderSlug::Hetzner,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.settings.providers.sync-regions', $provider))
        ->assertRedirect();

    expect(PlatformRegion::query()->where('provider_id', $provider->id)->count())->toBe(2);

    /** @var PlatformRegion $region */
    $region = PlatformRegion::query()->where('slug', 'fsn1')->sole();

    expect($region->name)->toBe('Falkenstein DC Park 1')
        ->and($region->country)->toBe('DE')
        ->and($region->city)->toBe('Falkenstein')
        ->and($region->is_available)->toBeTrue();
});

test('sync is rejected when provider has no api token', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var Provider $provider */
    $provider = Provider::factory()->create([
        'slug' => ProviderSlug::Hetzner,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.settings.providers.sync-regions', $provider))
        ->assertRedirect()
        ->assertSessionHasErrors('provider');
});

test('a non-platform administrator is forbidden from syncing regions', function () {
    /** @var User $user */
    $user = User::factory()->create(['platform_role' => PlatformRole::Member]);

    /** @var Provider $provider */
    $provider = Provider::factory()->withApiToken()->create([
        'slug' => ProviderSlug::Hetzner,
    ]);

    $this->actingAs($user)
        ->post(route('admin.settings.providers.sync-regions', $provider))
        ->assertForbidden();
});

test('a guest is redirected to login when syncing regions', function () {
    /** @var Provider $provider */
    $provider = Provider::factory()->withApiToken()->create([
        'slug' => ProviderSlug::Hetzner,
    ]);

    $this->post(route('admin.settings.providers.sync-regions', $provider))
        ->assertRedirect(route('login'));
});

test('the regions page includes the can sync_regions permission', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var Provider $provider */
    $provider = Provider::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.settings.providers.regions', $provider))
        ->assertOk()
        ->assertInertia(fn (Inertia\Testing\AssertableInertia $page) => $page
            ->component('admin/providers/regions')
            ->where('can.sync_regions', true)
        );
});
