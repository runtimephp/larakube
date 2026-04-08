<?php

declare(strict_types=1);

use App\Enums\PlatformRole;
use App\Enums\ProviderSlug;
use App\Models\PlatformRegion;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Facades\Http;
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

test('the show route redirects to overview', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var Provider $provider */
    $provider = Provider::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.settings.providers.show', $provider))
        ->assertRedirect(route('admin.settings.providers.overview', $provider));
});

test('a platform administrator can view the provider overview', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var Provider $provider */
    $provider = Provider::factory()->active()->create([
        'name' => 'Hetzner',
        'slug' => ProviderSlug::Hetzner,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.settings.providers.overview', $provider))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/providers/overview')
            ->where('provider.id', $provider->id)
            ->where('provider.name', 'Hetzner')
            ->where('provider.slug', 'hetzner')
            ->where('provider.is_active', true)
            ->has('provider.created_at')
        );
});

test('a platform administrator can view the provider regions', function () {
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
        ->get(route('admin.settings.providers.regions', $provider))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/providers/regions')
            ->where('provider.id', $provider->id)
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
        ->get(route('admin.settings.providers.regions', $provider))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/providers/regions')
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

test('the settings page includes the can update permission', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var Provider $provider */
    $provider = Provider::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.settings.providers.settings', $provider))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/providers/settings')
            ->where('can.update', true)
        );
});

test('a platform administrator can update a provider api token', function () {
    Http::fake(['api.hetzner.cloud/*' => Http::response([], 200)]);

    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var Provider $provider */
    $provider = Provider::factory()->create([
        'slug' => ProviderSlug::Hetzner,
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.settings.providers.update', $provider), [
            'api_token' => 'new-secret-token',
            'is_active' => false,
        ])
        ->assertRedirect();

    $provider->refresh();

    expect($provider->api_token)->toBe('new-secret-token');
});

test('updating a provider with an invalid api token is rejected', function () {
    Http::fake(['api.hetzner.cloud/*' => Http::response([], 403)]);

    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var Provider $provider */
    $provider = Provider::factory()->create([
        'slug' => ProviderSlug::Hetzner,
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.settings.providers.update', $provider), [
            'api_token' => 'bad-token',
            'is_active' => false,
        ])
        ->assertSessionHasErrors('api_token');
});

test('a platform administrator can toggle a provider active status', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var Provider $provider */
    $provider = Provider::factory()->create([
        'slug' => ProviderSlug::Hetzner,
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.settings.providers.update', $provider), [
            'is_active' => true,
        ])
        ->assertRedirect();

    $provider->refresh();

    expect($provider->is_active)->toBeTrue();
});

test('updating a provider without an api token does not clear the existing token', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var Provider $provider */
    $provider = Provider::factory()->withApiToken()->create([
        'slug' => ProviderSlug::Hetzner,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.settings.providers.update', $provider), [
            'api_token' => '',
            'is_active' => true,
        ])
        ->assertRedirect();

    $provider->refresh();

    expect($provider->api_token)->toBe('test-api-token');
});

test('a non-platform administrator is forbidden from updating a provider', function () {
    /** @var User $user */
    $user = User::factory()->create(['platform_role' => PlatformRole::Member]);

    /** @var Provider $provider */
    $provider = Provider::factory()->create();

    $this->actingAs($user)
        ->patch(route('admin.settings.providers.update', $provider), [
            'is_active' => true,
        ])
        ->assertForbidden();
});

test('a guest is redirected to login when updating a provider', function () {
    /** @var Provider $provider */
    $provider = Provider::factory()->create();

    $this->patch(route('admin.settings.providers.update', $provider), [
        'is_active' => true,
    ])
        ->assertRedirect(route('login'));
});

test('updating a provider requires is_active to be a boolean', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    /** @var Provider $provider */
    $provider = Provider::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.settings.providers.update', $provider), [
            'is_active' => 'not-a-boolean',
        ])
        ->assertSessionHasErrors('is_active');
});

test('the index page includes available slugs and can create permission', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    Provider::factory()->create(['slug' => ProviderSlug::Hetzner]);

    $this->actingAs($admin)
        ->get(route('admin.settings.providers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/providers/index')
            ->where('can.create', true)
            ->has('availableSlugs', 5)
            ->where('availableSlugs.0.value', 'digital_ocean')
            ->where('availableSlugs.0.label', 'DigitalOcean')
        );
});

test('a platform administrator can create a provider from a valid slug', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    $this->actingAs($admin)
        ->post(route('admin.settings.providers.store'), [
            'slug' => 'hetzner',
        ])
        ->assertRedirect();

    /** @var Provider $provider */
    $provider = Provider::query()->where('slug', 'hetzner')->sole();

    expect($provider->name)->toBe('Hetzner')
        ->and($provider->slug)->toBe(ProviderSlug::Hetzner)
        ->and($provider->is_active)->toBeFalse();
});

test('a platform administrator can create a provider with an api token', function () {
    Http::fake(['api.hetzner.cloud/*' => Http::response([], 200)]);

    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    $this->actingAs($admin)
        ->post(route('admin.settings.providers.store'), [
            'slug' => 'hetzner',
            'api_token' => 'my-secret-token',
        ])
        ->assertRedirect();

    /** @var Provider $provider */
    $provider = Provider::query()->where('slug', 'hetzner')->sole();

    expect($provider->api_token)->toBe('my-secret-token');
});

test('creating a provider with an invalid api token is rejected', function () {
    Http::fake(['api.hetzner.cloud/*' => Http::response([], 403)]);

    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    $this->actingAs($admin)
        ->post(route('admin.settings.providers.store'), [
            'slug' => 'hetzner',
            'api_token' => 'bad-token',
        ])
        ->assertSessionHasErrors('api_token');

    expect(Provider::query()->count())->toBe(0);
});

test('creating a provider derives the name from the slug label', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    $this->actingAs($admin)
        ->post(route('admin.settings.providers.store'), [
            'slug' => 'digital_ocean',
        ])
        ->assertRedirect();

    /** @var Provider $provider */
    $provider = Provider::query()->where('slug', 'digital_ocean')->sole();

    expect($provider->name)->toBe('DigitalOcean');
});

test('creating a provider with an invalid slug is rejected', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    $this->actingAs($admin)
        ->post(route('admin.settings.providers.store'), [
            'slug' => 'invalid-provider',
        ])
        ->assertSessionHasErrors('slug');

    expect(Provider::query()->count())->toBe(0);
});

test('creating a provider with a duplicate slug is rejected', function () {
    /** @var User $admin */
    $admin = User::factory()->create(['platform_role' => PlatformRole::Admin]);

    Provider::factory()->create(['slug' => ProviderSlug::Hetzner]);

    $this->actingAs($admin)
        ->post(route('admin.settings.providers.store'), [
            'slug' => 'hetzner',
        ])
        ->assertSessionHasErrors('slug');

    expect(Provider::query()->where('slug', 'hetzner')->count())->toBe(1);
});

test('a non-platform administrator is forbidden from creating a provider', function () {
    /** @var User $user */
    $user = User::factory()->create(['platform_role' => PlatformRole::Member]);

    $this->actingAs($user)
        ->post(route('admin.settings.providers.store'), [
            'slug' => 'hetzner',
        ])
        ->assertForbidden();
});

test('a guest is redirected to login when creating a provider', function () {
    $this->post(route('admin.settings.providers.store'), [
        'slug' => 'hetzner',
    ])
        ->assertRedirect(route('login'));
});
