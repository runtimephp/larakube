<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\ManifestData;
use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\ApplyManifest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('applies a manifest to the kubernetes cluster', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        ApplyManifest::class => MockResponse::fixture('kubernetes/apply-manifest'),
    ]);

    $connector->withMockClient($mockClient);

    $manifest = [
        'apiVersion' => 'v1',
        'kind' => 'ConfigMap',
        'metadata' => [
            'name' => 'cluster-config',
            'namespace' => 'kuven-test-ns',
        ],
        'data' => [
            'cluster-name' => 'my-workload-cluster',
            'kubernetes-version' => 'v1.29.0',
        ],
    ];

    $response = $connector->send(new ApplyManifest($manifest));

    $data = $response->dtoOrFail();

    expect($data)
        ->toBeInstanceOf(ManifestData::class)
        ->apiVersion->toBe('v1')
        ->kind->toBe('ConfigMap');

    expect($data->metadata)
        ->toBeInstanceOf(ResourceMetadata::class)
        ->name->toBe('cluster-config')
        ->namespace->toBe('kuven-test-ns')
        ->uid->toBeString()->not->toBeEmpty();
});

it('resolves the correct endpoint for core api resources', function (): void {
    $manifest = [
        'apiVersion' => 'v1',
        'kind' => 'ConfigMap',
        'metadata' => ['name' => 'test', 'namespace' => 'default'],
    ];

    $request = new ApplyManifest($manifest);

    expect($request->resolveEndpoint())->toBe('/api/v1/namespaces/default/configmaps');
});

it('resolves the correct endpoint for api group resources', function (): void {
    $manifest = [
        'apiVersion' => 'cluster.x-k8s.io/v1beta1',
        'kind' => 'Cluster',
        'metadata' => ['name' => 'my-cluster', 'namespace' => 'kuven-org-123'],
    ];

    $request = new ApplyManifest($manifest);

    expect($request->resolveEndpoint())->toBe('/apis/cluster.x-k8s.io/v1beta1/namespaces/kuven-org-123/clusters');
});

it('resolves the correct endpoint for cluster-scoped resources', function (): void {
    $manifest = [
        'apiVersion' => 'v1',
        'kind' => 'Namespace',
        'metadata' => ['name' => 'my-namespace'],
    ];

    $request = new ApplyManifest($manifest);

    expect($request->resolveEndpoint())->toBe('/api/v1/namespaces');
});

it('uses explicit resource name when provided', function (): void {
    $manifest = [
        'apiVersion' => 'infrastructure.cluster.x-k8s.io/v1beta1',
        'kind' => 'HetznerCluster',
        'metadata' => ['name' => 'my-cluster', 'namespace' => 'kuven-org-123'],
    ];

    $request = new ApplyManifest($manifest, resource: 'hetznerclusters');

    expect($request->resolveEndpoint())->toBe('/apis/infrastructure.cluster.x-k8s.io/v1beta1/namespaces/kuven-org-123/hetznerclusters');
});
