<?php

declare(strict_types=1);

use App\Enums\PlatformRole;
use App\Enums\ProviderSlug;
use App\Models\PlatformRegion;
use App\Models\Provider;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('a platform administrator can view the providers list', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var Provider $provider */
    $provider = Provider::factory()->active()->create([
        'name' => 'Hetzner',
        'slug' => ProviderSlug::Hetzner,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.settings.providers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/providers/index')
            ->has('providers', 1)
            ->where('providers.0.id', $provider->id)
            ->where('providers.0.name', 'Hetzner')
            ->where('providers.0.slug', 'hetzner')
            ->where('providers.0.is_active', true)
            ->has('providers.0.created_at')
        );
});

test('a platform administrator sees an empty list when no providers exist', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    $this->actingAs($admin)
        ->get(route('admin.settings.providers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/providers/index')
            ->has('providers', 0)
        );
});

test('a non-platform administrator is forbidden from accessing providers', function () {
    /** @var User $user */
    $user = User::factory()->create(['platform_role' => PlatformRole::Member]);

    $this->actingAs($user)
        ->get(route('admin.settings.providers.index'))
        ->assertForbidden();
});

test('a guest is redirected to login when accessing providers', function () {
    $this->get(route('admin.settings.providers.index'))
        ->assertRedirect(route('login'));
});

test('has_api_token is true when a provider has an api token', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    Provider::factory()->withApiToken()->create([
        'slug' => ProviderSlug::Hetzner,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.settings.providers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/providers/index')
            ->where('providers.0.has_api_token', true)
        );
});

test('has_api_token is false when a provider has no api token', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    Provider::factory()->create([
        'slug' => ProviderSlug::Hetzner,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.settings.providers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/providers/index')
            ->where('providers.0.has_api_token', false)
        );
});

test('a platform administrator can view a single provider with regions', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var Provider $provider */
    $provider = Provider::factory()->active()->create([
        'name' => 'Hetzner',
        'slug' => ProviderSlug::Hetzner,
    ]);

    /** @var PlatformRegion $region */
    $region = PlatformRegion::factory()->create([
        'provider_id' => $provider->id,
        'name' => 'Falkenstein',
        'slug' => 'fsn1',
        'country' => 'DE',
        'city' => 'Falkenstein',
        'is_available' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.settings.providers.show', $provider))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/providers/show')
            ->where('provider.id', $provider->id)
            ->where('provider.name', 'Hetzner')
            ->where('provider.slug', 'hetzner')
            ->where('provider.is_active', true)
            ->has('provider.created_at')
            ->has('regions', 1)
            ->where('regions.0.id', $region->id)
            ->where('regions.0.name', 'Falkenstein')
            ->where('regions.0.slug', 'fsn1')
            ->where('regions.0.country', 'DE')
            ->where('regions.0.city', 'Falkenstein')
            ->where('regions.0.is_available', true)
        );
});

test('a platform administrator sees empty regions for a provider without regions', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var Provider $provider */
    $provider = Provider::factory()->active()->create([
        'slug' => ProviderSlug::Hetzner,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.settings.providers.show', $provider))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/providers/show')
            ->has('regions', 0)
        );
});

test('a non-platform administrator is forbidden from viewing a provider', function () {
    /** @var User $user */
    $user = User::factory()->create(['platform_role' => PlatformRole::Member]);

    /** @var Provider $provider */
    $provider = Provider::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.settings.providers.show', $provider))
        ->assertForbidden();
});

test('a guest is redirected to login when viewing a provider', function () {
    /** @var Provider $provider */
    $provider = Provider::factory()->create();

    $this->get(route('admin.settings.providers.show', $provider))
        ->assertRedirect(route('login'));
});
