<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Models\Backup;
use App\Models\Infrastructure;
use Carbon\CarbonImmutable;

test('creates backup',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Backup $backup */
        $backup = Backup::factory()->create([
            'name' => 'daily-backup',
        ]);

        expect($backup->name)->toBe('daily-backup')
            ->and($backup->id)->toBeString()
            ->and($backup->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('belongs to infrastructure',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var Backup $backup */
        $backup = Backup::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($backup->infrastructure)->toBeInstanceOf(Infrastructure::class)
            ->and($backup->infrastructure->id)->toBe($infrastructure->id);
    });

test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Backup $backup */
        $backup = Backup::factory()->create([
            'status' => InfrastructureStatus::Healthy,
        ]);

        expect($backup->id)->toBeString()
            ->and($backup->created_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($backup->updated_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($backup->status)->toBe(InfrastructureStatus::Healthy);
    });

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Backup $backup */
        $backup = Backup::factory()->create();

        expect($backup->id)
            ->toBeString()
            ->toBeUuid();
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Backup $backup */
        $backup = Backup::factory()
            ->create()
            ->refresh();

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
