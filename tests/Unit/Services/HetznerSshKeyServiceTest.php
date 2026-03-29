<?php

declare(strict_types=1);

use App\Services\HetznerSshKeyService;
use Illuminate\Support\Facades\Http;

test('register throws on api error', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/ssh_keys' => Http::response([
            'error' => ['message' => 'invalid public key', 'code' => 'invalid_input'],
        ], 422),
    ]);

    $service = new HetznerSshKeyService('token');

    $service->register('deploy-key', 'invalid-key');
})->throws(RuntimeException::class, 'invalid public key');

test('register returns ssh key data', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/ssh_keys' => Http::response([
            'ssh_key' => [
                'id' => 123,
                'name' => 'deploy-key',
                'fingerprint' => 'ab:cd:ef:12:34',
                'public_key' => 'ssh-ed25519 AAAA...',
            ],
        ]),
    ]);

    $service = new HetznerSshKeyService('token');
    $key = $service->register('deploy-key', 'ssh-ed25519 AAAA...');

    expect($key->externalId)->toBe(123)
        ->and($key->name)->toBe('deploy-key')
        ->and($key->fingerprint)->toBe('ab:cd:ef:12:34')
        ->and($key->publicKey)->toBe('ssh-ed25519 AAAA...');
});

test('list returns collection of ssh key data', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/ssh_keys' => Http::response([
            'ssh_keys' => [
                [
                    'id' => 123,
                    'name' => 'deploy-key',
                    'fingerprint' => 'ab:cd:ef:12:34',
                    'public_key' => 'ssh-ed25519 AAAA...',
                ],
                [
                    'id' => 456,
                    'name' => 'bastion-key',
                    'fingerprint' => 'fe:dc:ba:56:78',
                    'public_key' => 'ssh-ed25519 BBBB...',
                ],
            ],
        ]),
    ]);

    $service = new HetznerSshKeyService('token');
    $keys = $service->list();

    expect($keys)->toHaveCount(2)
        ->and($keys[0]->externalId)->toBe(123)
        ->and($keys[1]->externalId)->toBe(456);
});

test('list throws on api error', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/ssh_keys' => Http::response([
            'error' => ['message' => 'unauthorized', 'code' => 'unauthorized'],
        ], 401),
    ]);

    $service = new HetznerSshKeyService('token');

    $service->list();
})->throws(RuntimeException::class, 'unauthorized');

test('delete returns true on success', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/ssh_keys/123' => Http::response([], 200),
    ]);

    $service = new HetznerSshKeyService('token');

    expect($service->delete(123))->toBeTrue();
});

test('delete returns false on failure', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/ssh_keys/999' => Http::response([], 404),
    ]);

    $service = new HetznerSshKeyService('token');

    expect($service->delete(999))->toBeFalse();
});
