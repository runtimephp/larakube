<?php

declare(strict_types=1);

use App\Models\Infrastructure;
use App\Models\SshKey;

test('to array', function (): void {

    /** @var SshKey $sshKey */
    $sshKey = SshKey::factory()
        ->create()
        ->fresh();

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

test('belongs to infrastructure', function (): void {
    /** @var SshKey $sshKey */
    $sshKey = SshKey::factory()->create();

    expect($sshKey->infrastructure)
        ->toBeInstanceOf(Infrastructure::class);
});
