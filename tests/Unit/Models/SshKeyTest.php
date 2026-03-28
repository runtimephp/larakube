<?php

declare(strict_types=1);

use App\Models\Infrastructure;
use App\Models\SshKey;
use Carbon\CarbonImmutable;

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
            ]);
    });
