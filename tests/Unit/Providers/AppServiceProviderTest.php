<?php

declare(strict_types=1);

use App\Client\HttpAuthClient;
use App\Client\LarakubeClient;
use App\Console\Services\SessionManager;
use App\Contracts\AuthClient;

test('larakube client is registered as singleton', function (): void {
    $client1 = app(LarakubeClient::class);
    $client2 = app(LarakubeClient::class);

    expect($client1)
        ->toBeInstanceOf(LarakubeClient::class)
        ->and($client1)->toBe($client2);
});

test('auth client is bound to http implementation', function (): void {
    $client = app(AuthClient::class);

    expect($client)->toBeInstanceOf(HttpAuthClient::class);
});

test('session manager is registered as singleton', function (): void {
    $session1 = app(SessionManager::class);
    $session2 = app(SessionManager::class);

    expect($session1)->toBe($session2);
});
