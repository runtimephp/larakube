<?php

declare(strict_types=1);

use App\Data\SshKeyData;
use App\Services\InMemory\InMemorySshKeyService;

test('delete throws when configured to throw on delete', function (): void {
    $service = new InMemorySshKeyService();
    $service->addKey(new SshKeyData(externalId: '123', name: 'key-1', fingerprint: 'fp-1', publicKey: 'pk-1'));
    $service->shouldThrowOnDelete();

    $service->delete('123');
})->throws(RuntimeException::class, 'Simulated API failure on delete');

test('register stores and returns ssh key data', function (): void {
    $service = new InMemorySshKeyService();
    $key = $service->register('deploy-key', 'ssh-ed25519 AAAA...');

    expect($key->name)->toBe('deploy-key')
        ->and($key->publicKey)->toBe('ssh-ed25519 AAAA...')
        ->and($service->list())->toHaveCount(1);
});

test('register throws when configured to fail', function (): void {
    $service = new InMemorySshKeyService();
    $service->shouldFailRegister();

    $service->register('deploy-key', 'ssh-ed25519 AAAA...');
})->throws(RuntimeException::class);

test('list returns all keys', function (): void {
    $service = new InMemorySshKeyService();
    $service->addKey(new SshKeyData(externalId: '1', name: 'key-1', fingerprint: 'fp-1', publicKey: 'pk-1'));
    $service->addKey(new SshKeyData(externalId: '2', name: 'key-2', fingerprint: 'fp-2', publicKey: 'pk-2'));

    expect($service->list())->toHaveCount(2);
});

test('delete removes key and returns true', function (): void {
    $service = new InMemorySshKeyService();
    $service->addKey(new SshKeyData(externalId: '123', name: 'key-1', fingerprint: 'fp-1', publicKey: 'pk-1'));

    expect($service->delete('123'))->toBeTrue()
        ->and($service->list())->toBeEmpty();
});

test('delete returns false when not found', function (): void {
    $service = new InMemorySshKeyService();

    expect($service->delete('nonexistent'))->toBeFalse();
});

test('delete returns false when configured to fail', function (): void {
    $service = new InMemorySshKeyService();
    $service->addKey(new SshKeyData(externalId: '123', name: 'key-1', fingerprint: 'fp-1', publicKey: 'pk-1'));
    $service->shouldFailDelete();

    expect($service->delete('123'))->toBeFalse();
});
