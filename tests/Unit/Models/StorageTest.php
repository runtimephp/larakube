<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;
use App\Models\Storage;

test('to array', function (): void {

    /** @var Storage $storage */
    $storage = Storage::factory()
        ->create()
        ->fresh();

    expect(array_keys($storage->toArray()))
        ->toBe([
            'id',
            'created_at',
            'updated_at',
            'infrastructure_id',
            'name',
            'external_volume_id',
            'size_gb',
            'status',
        ]);
});

test('status is cast to enum', function (): void {
    /** @var Storage $storage */
    $storage = Storage::factory()->create([
        'status' => InfrastructureStatus::Healthy,
    ]);

    expect($storage->status)->toBe(InfrastructureStatus::Healthy);
});

test('size gb is cast to integer', function (): void {
    /** @var Storage $storage */
    $storage = Storage::factory()->create([
        'size_gb' => 500,
    ]);

    expect($storage->size_gb)->toBe(500);
});

test('belongs to infrastructure', function (): void {
    /** @var Storage $storage */
    $storage = Storage::factory()->create();

    expect($storage->infrastructure)
        ->toBeInstanceOf(Infrastructure::class);
});
