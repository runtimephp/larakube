<?php

declare(strict_types=1);

use App\Models\CloudProvider;
use App\Models\Region;

test('to array', function (): void {

    /** @var Region $region */
    $region = Region::factory()
        ->create()
        ->fresh();

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

test('belongs to cloud provider', function (): void {
    /** @var Region $region */
    $region = Region::factory()->create();

    expect($region->cloudProvider)
        ->toBeInstanceOf(CloudProvider::class);
});

test('has many infrastructures', function (): void {
    /** @var Region $region */
    $region = Region::factory()->create();

    expect($region->infrastructures)->toBeEmpty();
});
