<?php

declare(strict_types=1);

use App\Actions\UpdateProvider;
use App\Enums\ProviderSlug;
use App\Models\Provider;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

test('it updates the active status', function () {
    /** @var Provider $provider */
    $provider = Provider::factory()->create(['slug' => ProviderSlug::Hetzner]);

    /** @var UpdateProvider $action */
    $action = app(UpdateProvider::class);

    $action->handle($provider, '', true);

    expect($provider->fresh()->is_active)->toBeTrue();
});

test('it updates the api token when provided', function () {
    Http::fake(['api.hetzner.cloud/*' => Http::response([], 200)]);

    /** @var Provider $provider */
    $provider = Provider::factory()->create(['slug' => ProviderSlug::Hetzner]);

    /** @var UpdateProvider $action */
    $action = app(UpdateProvider::class);

    $action->handle($provider, 'new-token', true);

    expect($provider->fresh())
        ->api_token->toBe('new-token')
        ->is_active->toBeTrue();

    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'api.hetzner.cloud'));
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

test('it throws a validation exception for an invalid api token', function () {
    Http::fake(['api.hetzner.cloud/*' => Http::response([], 403)]);

    /** @var Provider $provider */
    $provider = Provider::factory()->create(['slug' => ProviderSlug::Hetzner]);

    /** @var UpdateProvider $action */
    $action = app(UpdateProvider::class);

    $action->handle($provider, 'invalid-token', true);
})->throws(ValidationException::class);

test('it skips token validation for unsupported providers', function () {
    Http::fake();

    /** @var Provider $provider */
    $provider = Provider::factory()->create(['slug' => ProviderSlug::Aws]);

    /** @var UpdateProvider $action */
    $action = app(UpdateProvider::class);

    $action->handle($provider, 'some-token', true);

    expect($provider->fresh())
        ->api_token->toBe('some-token')
        ->is_active->toBeTrue();

    Http::assertNothingSent();
});
