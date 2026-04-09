<?php

declare(strict_types=1);

use App\Enums\PlatformRole;
use App\Models\PlatformRegion;
use App\Models\Provider;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'platform_role' => PlatformRole::Admin,
    ]);
});

test('renders create page', function (): void {
    /** @var Provider $hetzner */
    $hetzner = Provider::factory()
        ->hetzner()
        ->active()
        ->create()
        ->fresh();

    /** @var PlatformRegion $lisbon */
    $lisbon = PlatformRegion::factory()
        ->for($hetzner)
        ->create([
            'name' => 'lisbon',
        ]);

    Provider::factory()
        ->digitalOcean()
        ->active()
        ->create()
        ->fresh();

    Provider::factory()
        ->akamai()
        ->create();

    $this->actingAs($this->user)
        ->get(route('admin.management-clusters.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin-management-clusters/create')
            ->has('providers', 2)
            ->where('providers.0.id', $hetzner->id)
            ->where('providers.0.name', $hetzner->name)
            ->where('providers.0.regions.0.id', $lisbon->id)
            ->etc()
        );
});

test('member cannot create management clusters', function (): void {
    /** @var User $user */
    $user = User::factory()->create([
        'platform_role' => PlatformRole::Member,
    ]);

    $this->actingAs($user)
        ->get(route('admin.management-clusters.create'))
        ->assertForbidden();
});

test('admin can store a management cluster', function (): void {
    /** @var Provider $provider */
    $provider = Provider::factory()->hetzner()->active()->create();

    /** @var PlatformRegion $region */
    $region = PlatformRegion::factory()->for($provider)->create();

    $this->actingAs($this->user)
        ->post(route('admin.management-clusters.store'), [
            'name' => 'mgmt-production',
            'provider_id' => $provider->id,
            'region_id' => $region->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('management_clusters', [
        'name' => 'mgmt-production',
        'provider_id' => $provider->id,
        'platform_region_id' => $region->id,
    ]);
});

test('store validates required fields', function (): void {
    $this->actingAs($this->user)
        ->post(route('admin.management-clusters.store'), [])
        ->assertSessionHasErrors(['name', 'provider_id', 'region_id']);
});

test('store validates provider exists', function (): void {
    /** @var Provider $provider */
    $provider = Provider::factory()->hetzner()->create();

    /** @var PlatformRegion $region */
    $region = PlatformRegion::factory()->for($provider)->create();

    $this->actingAs($this->user)
        ->post(route('admin.management-clusters.store'), [
            'name' => 'mgmt-production',
            'provider_id' => 'non-existent-id',
            'region_id' => $region->id,
        ])
        ->assertSessionHasErrors('provider_id');
});

test('store validates region exists in platform_regions', function (): void {
    /** @var Provider $provider */
    $provider = Provider::factory()->hetzner()->create();

    $this->actingAs($this->user)
        ->post(route('admin.management-clusters.store'), [
            'name' => 'mgmt-production',
            'provider_id' => $provider->id,
            'region_id' => 'non-existent-id',
        ])
        ->assertSessionHasErrors('region_id');
});

test('member cannot store a management cluster', function (): void {
    /** @var User $user */
    $user = User::factory()->create([
        'platform_role' => PlatformRole::Member,
    ]);

    $this->actingAs($user)
        ->post(route('admin.management-clusters.store'), [
            'name' => 'mgmt-production',
        ])
        ->assertForbidden();
});
