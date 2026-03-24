<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;
use App\Models\LoadBalancer;

test('to array', function (): void {

    /** @var LoadBalancer $loadBalancer */
    $loadBalancer = LoadBalancer::factory()
        ->create()
        ->fresh();

    expect(array_keys($loadBalancer->toArray()))
        ->toBe([
            'id',
            'created_at',
            'updated_at',
            'infrastructure_id',
            'name',
            'external_load_balancer_id',
            'ip',
            'status',
        ]);
});

test('status is cast to enum', function (): void {
    /** @var LoadBalancer $loadBalancer */
    $loadBalancer = LoadBalancer::factory()->create([
        'status' => InfrastructureStatus::Healthy,
    ]);

    expect($loadBalancer->status)->toBe(InfrastructureStatus::Healthy);
});

test('belongs to infrastructure', function (): void {
    /** @var LoadBalancer $loadBalancer */
    $loadBalancer = LoadBalancer::factory()->create();

    expect($loadBalancer->infrastructure)
        ->toBeInstanceOf(Infrastructure::class);
});
