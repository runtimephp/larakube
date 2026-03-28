<?php

declare(strict_types=1);

use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Region;
use Carbon\CarbonImmutable;

test('creates region',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Region $region */
        $region = Region::factory()->create([
            'internal_name' => 'eu-central',
        ]);

        expect($region->internal_name)->toBe('eu-central')
            ->and($region->id)->toBeString()
            ->and($region->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('belongs to cloud provider',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $cloudProvider */
        $cloudProvider = CloudProvider::factory()->create();

        /** @var Region $region */
        $region = Region::factory()->create([
            'cloud_provider_id' => $cloudProvider->id,
        ]);

        expect($region->cloudProvider)->toBeInstanceOf(CloudProvider::class)
            ->and($region->cloudProvider->id)->toBe($cloudProvider->id);
    });

test('has many infrastructures',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Region $region */
        $region = Region::factory()->create();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create([
            'region_id' => $region->id,
        ]);

        expect($region->infrastructures)->toHaveCount(1)
            ->and($region->infrastructures->first())->toBeInstanceOf(Infrastructure::class)
            ->and($region->infrastructures->first()->id)->toBe($infrastructure->id);
    });

test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Region $region */
        $region = Region::factory()->create();

        expect($region->id)->toBeString()
            ->and($region->created_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($region->updated_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Region $region */
        $region = Region::factory()->create();

        expect($region->id)
            ->toBeString()
            ->toBeUuid();
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Region $region */
        $region = Region::factory()
            ->create()
            ->refresh();

        expect(array_keys($region->toArray()))
            ->toBe([
                'id',
                'created_at',
                'updated_at',
                'cloud_provider_id',
                'internal_name',
                'provider_region',
                'description',
            ]);
    });
