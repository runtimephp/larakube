<?php

declare(strict_types=1);

use App\Enums\ProviderSlug;
use App\Rules\ValidProviderToken;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

test('it passes for a valid hetzner token', function () {
    Http::fake(['api.hetzner.cloud/*' => Http::response([], 200)]);

    $validator = Validator::make(
        ['api_token' => 'valid-token'],
        ['api_token' => [new ValidProviderToken(ProviderSlug::Hetzner)]],
    );

    expect($validator->passes())->toBeTrue();

    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'api.hetzner.cloud'));
});

test('it fails for an invalid hetzner token', function () {
    Http::fake(['api.hetzner.cloud/*' => Http::response([], 403)]);

    $validator = Validator::make(
        ['api_token' => 'invalid-token'],
        ['api_token' => [new ValidProviderToken(ProviderSlug::Hetzner)]],
    );

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first('api_token'))->toContain('invalid');
});

test('it passes for a valid digital ocean token', function () {
    Http::fake(['api.digitalocean.com/*' => Http::response([], 200)]);

    $validator = Validator::make(
        ['api_token' => 'valid-token'],
        ['api_token' => [new ValidProviderToken(ProviderSlug::DigitalOcean)]],
    );

    expect($validator->passes())->toBeTrue();

    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'api.digitalocean.com'));
});

test('it fails for an invalid digital ocean token', function () {
    Http::fake(['api.digitalocean.com/*' => Http::response([], 403)]);

    $validator = Validator::make(
        ['api_token' => 'invalid-token'],
        ['api_token' => [new ValidProviderToken(ProviderSlug::DigitalOcean)]],
    );

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first('api_token'))->toContain('invalid');
});

test('it fails when the provider api is unreachable', function () {
    Http::fake([
        'api.hetzner.cloud/*' => fn () => throw new ConnectionException('Connection refused'),
    ]);

    $validator = Validator::make(
        ['api_token' => 'valid-token'],
        ['api_token' => [new ValidProviderToken(ProviderSlug::Hetzner)]],
    );

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->first('api_token'))->toContain('try again');
});

test('it skips validation for providers without a validation service', function () {
    Http::fake();

    $validator = Validator::make(
        ['api_token' => 'any-token'],
        ['api_token' => [new ValidProviderToken(ProviderSlug::Aws)]],
    );

    expect($validator->passes())->toBeTrue();

    Http::assertNothingSent();
});

test('it skips validation for empty tokens', function () {
    Http::fake();

    $validator = Validator::make(
        ['api_token' => ''],
        ['api_token' => [new ValidProviderToken(ProviderSlug::Hetzner)]],
    );

    expect($validator->passes())->toBeTrue();

    Http::assertNothingSent();
});
