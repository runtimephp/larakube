<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Models\Backup;
use App\Models\Infrastructure;

test('to array', function (): void {

    /** @var Backup $backup */
    $backup = Backup::factory()
        ->create()
        ->fresh();

    expect(array_keys($backup->toArray()))
        ->toBe([
            'id',
            'created_at',
            'updated_at',
            'infrastructure_id',
            'name',
            'external_backup_id',
            'status',
        ]);
});

test('status is cast to enum', function (): void {
    /** @var Backup $backup */
    $backup = Backup::factory()->create([
        'status' => InfrastructureStatus::Healthy,
    ]);

    expect($backup->status)->toBe(InfrastructureStatus::Healthy);
});

test('belongs to infrastructure', function (): void {
    /** @var Backup $backup */
    $backup = Backup::factory()->create();

    expect($backup->infrastructure)
        ->toBeInstanceOf(Infrastructure::class);
});
