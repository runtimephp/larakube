<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\ClusterData;
use App\Http\Integrations\Kubernetes\Data\ConditionData;
use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\GetCluster;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('gets a capi cluster from the kubernetes cluster', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        GetCluster::class => MockResponse::fixture('kubernetes/get-cluster'),
    ]);

    $connector->withMockClient($mockClient);

    $response = $connector->send(new GetCluster(
        name: 'test-workload',
        namespace: 'kuven-test-ns',
    ));

    $data = $response->dtoOrFail();

    expect($data)
        ->toBeInstanceOf(ClusterData::class)
        ->and($data->phase)->toBeString()
        ->and($data->conditions)->toBeArray()
        ->and($data->metadata)->toBeInstanceOf(ResourceMetadata::class)
        ->and($data->metadata->name)->toBe('test-workload')
        ->and($data->metadata->namespace)->toBe('kuven-test-ns')
        ->and($data->metadata->uid)->toBeString()
        ->and(mb_strlen((string) $data->metadata->uid))->toBeGreaterThan(0);
});

it('exposes cluster readiness from conditions', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        GetCluster::class => MockResponse::fixture('kubernetes/get-cluster'),
    ]);

    $connector->withMockClient($mockClient);

    $response = $connector->send(new GetCluster(
        name: 'test-workload',
        namespace: 'kuven-test-ns',
    ));

    $data = $response->dtoOrFail();

    expect($data->isReady())->toBeBool();

    if ($data->conditions !== []) {
        expect($data->conditions[0])->toBeInstanceOf(ConditionData::class);
    }
});
