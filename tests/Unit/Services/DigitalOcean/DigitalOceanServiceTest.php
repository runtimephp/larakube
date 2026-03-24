<?php

declare(strict_types=1);

use App\Services\DigitalOcean\DigitalOceanService;
use Illuminate\Support\Facades\Http;

test('validate token returns true on success', function (): void {
    Http::fake(['api.digitalocean.com/*' => Http::response([], 200)]);

    $service = new DigitalOceanService;

    expect($service->validateToken('valid-token'))->toBeTrue();
});

test('validate token returns false on failure', function (): void {
    Http::fake(['api.digitalocean.com/*' => Http::response([], 401)]);

    $service = new DigitalOceanService;

    expect($service->validateToken('invalid-token'))->toBeFalse();
});
