<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;
use App\Models\Network;

test('to array', function (): void {

    /** @var Network $network */
    $network = Network::factory()
        ->create()
        ->fresh();

    expect(array_keys($network->toArray()))
        ->toBe([
            'id',
            'created_at',
            'updated_at',
            'infrastructure_id',
            'name',
            'external_network_id',
            'cidr',
            'status',
        ]);
});

test('status is cast to enum', function (): void {
    /** @var Network $network */
    $network = Network::factory()->create([
        'status' => InfrastructureStatus::Healthy,
    ]);

    expect($network->status)->toBe(InfrastructureStatus::Healthy);
});

test('belongs to infrastructure', function (): void {
    /** @var Network $network */
    $network = Network::factory()->create();

    expect($network->infrastructure)
        ->toBeInstanceOf(Infrastructure::class);
});
