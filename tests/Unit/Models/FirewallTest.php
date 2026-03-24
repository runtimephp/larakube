<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Models\Firewall;
use App\Models\Infrastructure;

test('to array', function (): void {

    /** @var Firewall $firewall */
    $firewall = Firewall::factory()
        ->create()
        ->fresh();

    expect(array_keys($firewall->toArray()))
        ->toBe([
            'id',
            'created_at',
            'updated_at',
            'infrastructure_id',
            'name',
            'external_firewall_id',
            'status',
        ]);
});

test('status is cast to enum', function (): void {
    /** @var Firewall $firewall */
    $firewall = Firewall::factory()->create([
        'status' => InfrastructureStatus::Healthy,
    ]);

    expect($firewall->status)->toBe(InfrastructureStatus::Healthy);
});

test('belongs to infrastructure', function (): void {
    /** @var Firewall $firewall */
    $firewall = Firewall::factory()->create();

    expect($firewall->infrastructure)
        ->toBeInstanceOf(Infrastructure::class);
});
