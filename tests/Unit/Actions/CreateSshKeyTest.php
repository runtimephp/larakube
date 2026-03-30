<?php

declare(strict_types=1);

use App\Actions\CreateSshKey;
use App\Enums\SshKeyPurpose;
use App\Models\Infrastructure;
use App\Models\SshKey;

test('creates ssh key and returns model',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly();

        $action = new CreateSshKey();
        $sshKey = $action->handle(
            infrastructure: $infrastructure,
            name: 'test-bastion',
            fingerprint: 'ab:cd:ef',
            publicKey: 'ssh-ed25519 AAAA...',
            purpose: SshKeyPurpose::Bastion,
            privateKey: 'private-key-content',
        );

        expect($sshKey)->toBeInstanceOf(SshKey::class)
            ->and($sshKey->infrastructure_id)->toBe($infrastructure->id)
            ->and($sshKey->name)->toBe('test-bastion')
            ->and($sshKey->fingerprint)->toBe('ab:cd:ef')
            ->and($sshKey->public_key)->toBe('ssh-ed25519 AAAA...')
            ->and($sshKey->purpose)->toBe(SshKeyPurpose::Bastion)
            ->and($sshKey->private_key)->toBe('private-key-content');
    });

test('creates ssh key without private key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly();

        $action = new CreateSshKey();
        $sshKey = $action->handle(
            infrastructure: $infrastructure,
            name: 'test-node',
            fingerprint: 'fe:dc:ba',
            publicKey: 'ssh-ed25519 BBBB...',
            purpose: SshKeyPurpose::Node,
        );

        expect($sshKey->private_key)->toBeNull()
            ->and($sshKey->purpose)->toBe(SshKeyPurpose::Node);
    });
