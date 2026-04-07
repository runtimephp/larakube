<?php

declare(strict_types=1);

use App\Actions\CreateProvider;
use App\Enums\ProviderSlug;
use App\Models\Provider;

test('it creates a provider without an api token', function () {
    /** @var CreateProvider $action */
    $action = app(CreateProvider::class);

    $provider = $action->handle(ProviderSlug::Hetzner, '');

    expect($provider)
        ->toBeInstanceOf(Provider::class)
        ->name->toBe('Hetzner')
        ->slug->toBe(ProviderSlug::Hetzner)
        ->is_active->toBeFalse()
        ->api_token->toBeNull();
});

test('it creates a provider with an api token', function () {
    /** @var CreateProvider $action */
    $action = app(CreateProvider::class);

    $provider = $action->handle(ProviderSlug::Hetzner, 'some-token');

    expect($provider)
        ->toBeInstanceOf(Provider::class)
        ->name->toBe('Hetzner')
        ->slug->toBe(ProviderSlug::Hetzner)
        ->api_token->toBe('some-token');
});

test('it sets the provider name from the slug label', function () {
    /** @var CreateProvider $action */
    $action = app(CreateProvider::class);

    $provider = $action->handle(ProviderSlug::DigitalOcean, '');

    expect($provider->name)->toBe('DigitalOcean');
});

test('it sets the provider as inactive by default', function () {
    /** @var CreateProvider $action */
    $action = app(CreateProvider::class);

    $provider = $action->handle(ProviderSlug::Aws, '');

    expect($provider->is_active)->toBeFalse();
});
