<?php

declare(strict_types=1);

use App\Services\MultipassSshKeyService;

test('register returns ssh key data with provided values', function (): void {
    $service = new MultipassSshKeyService();
    $key = $service->register('deploy-key', 'ssh-ed25519 AAAA...');

    expect($key->name)->toBe('deploy-key')
        ->and($key->publicKey)->toBe('ssh-ed25519 AAAA...')
        ->and($key->externalId)->toBe('multipass-deploy-key');
});

test('list returns empty collection', function (): void {
    $service = new MultipassSshKeyService();

    expect($service->list())->toBeEmpty();
});

test('delete returns true', function (): void {
    $service = new MultipassSshKeyService();

    expect($service->delete('any-id'))->toBeTrue();
});
