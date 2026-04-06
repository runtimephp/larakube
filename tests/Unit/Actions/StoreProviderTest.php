<?php

declare(strict_types=1);

use App\Actions\StoreProvider;
use App\Enums\ProviderSlug;
use App\Models\Provider;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

test('it creates a provider without an api token', function () {
    /** @var StoreProvider $action */
    $action = app(StoreProvider::class);

    $provider = $action->handle(ProviderSlug::Hetzner, '');

    expect($provider)
        ->toBeInstanceOf(Provider::class)
        ->name->toBe('Hetzner')
        ->slug->toBe(ProviderSlug::Hetzner)
        ->is_active->toBeFalse()
        ->api_token->toBeNull();
});

test('it creates a provider with a valid api token', function () {
    Http::fake(['api.hetzner.cloud/*' => Http::response([], 200)]);

    /** @var StoreProvider $action */
    $action = app(StoreProvider::class);

    $provider = $action->handle(ProviderSlug::Hetzner, 'valid-token');

    expect($provider)
        ->toBeInstanceOf(Provider::class)
        ->name->toBe('Hetzner')
        ->slug->toBe(ProviderSlug::Hetzner)
        ->api_token->toBe('valid-token');

    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'api.hetzner.cloud'));
});

test('it throws a validation exception for an invalid api token', function () {
    Http::fake(['api.hetzner.cloud/*' => Http::response([], 403)]);

    /** @var StoreProvider $action */
    $action = app(StoreProvider::class);

    $action->handle(ProviderSlug::Hetzner, 'invalid-token');
})->throws(ValidationException::class);

test('it skips token validation for unsupported providers', function () {
    Http::fake();

    /** @var StoreProvider $action */
    $action = app(StoreProvider::class);

    $provider = $action->handle(ProviderSlug::Aws, 'some-token');

    expect($provider)
        ->name->toBe('AWS')
        ->slug->toBe(ProviderSlug::Aws)
        ->api_token->toBe('some-token');

    Http::assertNothingSent();
});

test('it sets the provider name from the slug label', function () {
    /** @var StoreProvider $action */
    $action = app(StoreProvider::class);

    $provider = $action->handle(ProviderSlug::DigitalOcean, '');

    expect($provider->name)->toBe('DigitalOcean');
});
