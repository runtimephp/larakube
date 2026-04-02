<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\ClusterData;
use App\Http\Integrations\Kubernetes\Data\ConditionData;
use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use Carbon\CarbonImmutable;

it('reports ready when Ready condition is true', function (): void {
    $cluster = new ClusterData(
        metadata: new ResourceMetadata(
            name: 'test',
            uid: 'uid-123',
            resourceVersion: '1',
            creationTimestamp: CarbonImmutable::now(),
        ),
        phase: 'Provisioned',
        conditions: [
            new ConditionData(type: 'InfrastructureReady', status: 'True'),
            new ConditionData(type: 'Ready', status: 'True'),
        ],
    );

    expect($cluster->isReady())->toBeTrue();
});

it('reports not ready when Ready condition is false', function (): void {
    $cluster = new ClusterData(
        metadata: new ResourceMetadata(
            name: 'test',
            uid: 'uid-123',
            resourceVersion: '1',
            creationTimestamp: CarbonImmutable::now(),
        ),
        phase: 'Provisioning',
        conditions: [
            new ConditionData(type: 'Ready', status: 'False', reason: 'NotReady'),
        ],
    );

    expect($cluster->isReady())->toBeFalse();
});

it('reports not ready when no conditions exist', function (): void {
    $cluster = new ClusterData(
        metadata: new ResourceMetadata(
            name: 'test',
            uid: 'uid-123',
            resourceVersion: '1',
            creationTimestamp: CarbonImmutable::now(),
        ),
        phase: 'Pending',
    );

    expect($cluster->isReady())->toBeFalse();
});

it('parses condition with all fields', function (): void {
    $condition = ConditionData::fromKubernetesResponse([
        'type' => 'Available',
        'status' => 'False',
        'reason' => 'NotAvailable',
        'message' => 'Cluster is provisioning',
        'lastTransitionTime' => '2026-04-02T18:00:00Z',
    ]);

    expect($condition)
        ->type->toBe('Available')
        ->status->toBe('False')
        ->reason->toBe('NotAvailable')
        ->message->toBe('Cluster is provisioning')
        ->lastTransitionTime->toBeInstanceOf(CarbonImmutable::class)
        ->isTrue()->toBeFalse();
});

it('parses condition without optional fields', function (): void {
    $condition = ConditionData::fromKubernetesResponse([
        'type' => 'Ready',
        'status' => 'True',
    ]);

    expect($condition)
        ->reason->toBeNull()
        ->message->toBeNull()
        ->lastTransitionTime->toBeNull()
        ->isTrue()->toBeTrue();
});
