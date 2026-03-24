<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;
use App\Models\KubernetesCluster;

test('to array', function (): void {

    /** @var KubernetesCluster $cluster */
    $cluster = KubernetesCluster::factory()
        ->create()
        ->fresh();

    expect(array_keys($cluster->toArray()))
        ->toBe([
            'id',
            'created_at',
            'updated_at',
            'infrastructure_id',
            'name',
            'version',
            'external_cluster_id',
            'status',
        ]);
});

test('status is cast to enum', function (): void {
    /** @var KubernetesCluster $cluster */
    $cluster = KubernetesCluster::factory()->create([
        'status' => InfrastructureStatus::Healthy,
    ]);

    expect($cluster->status)->toBe(InfrastructureStatus::Healthy);
});

test('belongs to infrastructure', function (): void {
    /** @var KubernetesCluster $cluster */
    $cluster = KubernetesCluster::factory()->create();

    expect($cluster->infrastructure)
        ->toBeInstanceOf(Infrastructure::class);
});

test('has many nodes', function (): void {
    /** @var KubernetesCluster $cluster */
    $cluster = KubernetesCluster::factory()->create();

    expect($cluster->nodes)->toBeEmpty();
});
