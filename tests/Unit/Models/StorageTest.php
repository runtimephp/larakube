<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;
use App\Models\Storage;
use Carbon\CarbonImmutable;

test('creates storage',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Storage $storage */
        $storage = Storage::factory()->create([
            'name' => 'data-vol',
        ]);

        expect($storage->name)->toBe('data-vol')
            ->and($storage->id)->toBeString()
            ->and($storage->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('belongs to infrastructure',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var Storage $storage */
        $storage = Storage::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($storage->infrastructure)->toBeInstanceOf(Infrastructure::class)
            ->and($storage->infrastructure->id)->toBe($infrastructure->id);
    });

test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Storage $storage */
        $storage = Storage::factory()->create([
            'status' => InfrastructureStatus::Healthy,
            'size_gb' => 500,
        ]);

        expect($storage->id)->toBeString()
            ->and($storage->created_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($storage->updated_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($storage->status)->toBe(InfrastructureStatus::Healthy)
            ->and($storage->size_gb)->toBe(500);
    });

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Storage $storage */
        $storage = Storage::factory()->create();

        expect($storage->id)
            ->toBeString()
            ->toBeUuid();
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Storage $storage */
        $storage = Storage::factory()
            ->create()
            ->refresh();

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
