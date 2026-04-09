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

    /** @var Provider $digitalOcean */
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

    $response = $this->actingAs($user)
        ->get(route('admin.management-clusters.create'));

    expect($response->getStatusCode())
        ->toBe(403);

});
