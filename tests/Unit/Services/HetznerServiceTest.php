<?php

declare(strict_types=1);

use App\Services\HetznerService;
use Illuminate\Support\Facades\Http;

test('validate token returns true on success', function (): void {
    Http::fake(['api.hetzner.cloud/*' => Http::response([], 200)]);

    $service = new HetznerService('valid-token');

    expect($service->validateToken())->toBeTrue();
});

test('validate token returns false on failure', function (): void {
    Http::fake(['api.hetzner.cloud/*' => Http::response([], 401)]);

    $service = new HetznerService('invalid-token');

    expect($service->validateToken())->toBeFalse();
});
