<?php

declare(strict_types=1);

use App\Actions\ValidateProviderToken;
use App\Enums\ProviderSlug;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

test('it validates a hetzner token successfully', function () {
    Http::fake(['api.hetzner.cloud/*' => Http::response([], 200)]);

    $action = new ValidateProviderToken();

    $action->handle(ProviderSlug::Hetzner, 'valid-token');

    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'api.hetzner.cloud'));
});

test('it throws a validation exception for an invalid hetzner token', function () {
    Http::fake(['api.hetzner.cloud/*' => Http::response([], 403)]);

    $action = new ValidateProviderToken();

    $action->handle(ProviderSlug::Hetzner, 'invalid-token');
})->throws(ValidationException::class);

test('it validates a digital ocean token successfully', function () {
    Http::fake(['api.digitalocean.com/*' => Http::response([], 200)]);

    $action = new ValidateProviderToken();

    $action->handle(ProviderSlug::DigitalOcean, 'valid-token');

    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'api.digitalocean.com'));
});

test('it throws a validation exception for an invalid digital ocean token', function () {
    Http::fake(['api.digitalocean.com/*' => Http::response([], 403)]);

    $action = new ValidateProviderToken();

    $action->handle(ProviderSlug::DigitalOcean, 'invalid-token');
})->throws(ValidationException::class);

test('it skips validation for providers without a validation service', function () {
    Http::fake();

    $action = new ValidateProviderToken();

    $action->handle(ProviderSlug::Aws, 'any-token');

    Http::assertNothingSent();
});
