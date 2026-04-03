<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\ClusterData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\ListClusters;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('lists capi clusters in a namespace', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        ListClusters::class => MockResponse::fixture('kubernetes/list-clusters'),
    ]);

    $connector->withMockClient($mockClient);

    $response = $connector->send(new ListClusters('kuven-test-ns'));

    $clusters = $response->dtoOrFail();

    expect($clusters)
        ->toBeArray()
        ->and(count($clusters))->toBeGreaterThan(0)
        ->and($clusters[0])->toBeInstanceOf(ClusterData::class)
        ->and($clusters[0]->phase)->toBeString()
        ->and($clusters[0]->metadata->name)->toBe('test-workload')
        ->and($clusters[0]->metadata->namespace)->toBe('kuven-test-ns');
});
