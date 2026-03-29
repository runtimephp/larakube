<?php

declare(strict_types=1);

use App\Enums\SshKeyPurpose;
use App\Models\Infrastructure;
use App\Models\SshKey;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

test('bastion factory state sets purpose and generates private key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var SshKey $sshKey */
        $sshKey = SshKey::factory()->bastion()->createQuietly();

        expect($sshKey->purpose)->toBe(SshKeyPurpose::Bastion)
            ->and($sshKey->private_key)->toBeString()
            ->and($sshKey->private_key)->not->toBeEmpty();
    });

test('node factory state sets purpose and null private key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var SshKey $sshKey */
        $sshKey = SshKey::factory()->node()->createQuietly();

        expect($sshKey->purpose)->toBe(SshKeyPurpose::Node)
            ->and($sshKey->private_key)->toBeNull();
    });

test('creates bastion ssh key with private key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var SshKey $sshKey */
        $sshKey = SshKey::factory()->createQuietly([
            'purpose' => SshKeyPurpose::Bastion,
            'private_key' => 'secret-private-key-content',
        ]);

        expect($sshKey->purpose)->toBe(SshKeyPurpose::Bastion)
            ->and($sshKey->private_key)->toBe('secret-private-key-content');
    });

test('creates node ssh key without private key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var SshKey $sshKey */
        $sshKey = SshKey::factory()->createQuietly([
            'purpose' => SshKeyPurpose::Node,
            'private_key' => null,
        ]);

        expect($sshKey->purpose)->toBe(SshKeyPurpose::Node)
            ->and($sshKey->private_key)->toBeNull();
    });

test('encrypts private key at rest',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var SshKey $sshKey */
        $sshKey = SshKey::factory()->createQuietly([
            'private_key' => 'secret-private-key-content',
        ]);

        $raw = DB::table('ssh_keys')
            ->where('id', $sshKey->id)
            ->value('private_key');

        expect($raw)->not->toBe('secret-private-key-content')
            ->and($sshKey->private_key)->toBe('secret-private-key-content');
    });

test('creates ssh key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var SshKey $sshKey */
        $sshKey = SshKey::factory()->create([
            'name' => 'deploy-key',
        ]);

        expect($sshKey->name)->toBe('deploy-key')
            ->and($sshKey->id)->toBeString()
            ->and($sshKey->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('belongs to infrastructure',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var SshKey $sshKey */
        $sshKey = SshKey::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($sshKey->infrastructure)->toBeInstanceOf(Infrastructure::class)
            ->and($sshKey->infrastructure->id)->toBe($infrastructure->id);
    });

test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var SshKey $sshKey */
        $sshKey = SshKey::factory()->create();

        expect($sshKey->id)->toBeString()
            ->and($sshKey->created_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($sshKey->updated_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var SshKey $sshKey */
        $sshKey = SshKey::factory()->create();

        expect($sshKey->id)
            ->toBeString()
            ->toBeUuid();
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var SshKey $sshKey */
        $sshKey = SshKey::factory()
            ->create()
            ->refresh();

        expect(array_keys($sshKey->toArray()))
            ->toBe([
                'id',
                'created_at',
                'updated_at',
                'infrastructure_id',
                'name',
                'fingerprint',
                'public_key',
                'purpose',
                'private_key',
            ]);
    });
