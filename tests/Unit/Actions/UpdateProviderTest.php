<?php

declare(strict_types=1);

use App\Actions\UpdateProvider;
use App\Enums\ProviderSlug;
use App\Models\Provider;

test('it updates the active status', function () {
    /** @var Provider $provider */
    $provider = Provider::factory()->create(['slug' => ProviderSlug::Hetzner]);

    /** @var UpdateProvider $action */
    $action = app(UpdateProvider::class);

    $action->handle($provider, '', true);

    expect($provider->fresh()->is_active)->toBeTrue();
});

test('it updates the api token when provided', function () {
    /** @var Provider $provider */
    $provider = Provider::factory()->create(['slug' => ProviderSlug::Hetzner]);

    /** @var UpdateProvider $action */
    $action = app(UpdateProvider::class);

    $action->handle($provider, 'new-token', true);

    expect($provider->fresh())
        ->api_token->toBe('new-token')
        ->is_active->toBeTrue();
});

test('it does not update the api token when empty', function () {
    /** @var Provider $provider */
    $provider = Provider::factory()->withApiToken()->create(['slug' => ProviderSlug::Hetzner]);

    /** @var UpdateProvider $action */
    $action = app(UpdateProvider::class);

    $action->handle($provider, '', false);

    expect($provider->fresh())
        ->api_token->toBe('test-api-token')
        ->is_active->toBeFalse();
});
