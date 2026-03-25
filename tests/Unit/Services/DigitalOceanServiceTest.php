<?php

declare(strict_types=1);

use App\Services\DigitalOceanService;
use Illuminate\Support\Facades\Http;

test('validate token returns true on success', function (): void {
    Http::fake(['api.digitalocean.com/*' => Http::response([], 200)]);

    $service = new DigitalOceanService('valid-token');

    expect($service->validateToken())->toBeTrue();
});

test('validate token returns false on failure', function (): void {
    Http::fake(['api.digitalocean.com/*' => Http::response([], 401)]);

    $service = new DigitalOceanService('invalid-token');

    expect($service->validateToken())->toBeFalse();
});
