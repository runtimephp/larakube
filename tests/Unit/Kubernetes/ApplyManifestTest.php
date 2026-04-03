<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\ManifestData;
use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Manifests\ContainerSpec;
use App\Http\Integrations\Kubernetes\Manifests\DeploymentManifest;
use App\Http\Integrations\Kubernetes\Manifests\DeploymentSpec;
use App\Http\Integrations\Kubernetes\Manifests\LabelSelector;
use App\Http\Integrations\Kubernetes\Manifests\LabelSet;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\NamespaceManifest;
use App\Http\Integrations\Kubernetes\Manifests\PodSpec;
use App\Http\Integrations\Kubernetes\Manifests\PodTemplateSpec;
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
        ->and($data->apiVersion)->toBe('v1')
        ->and($data->kind)->toBe('ConfigMap')
        ->and($data->metadata)->toBeInstanceOf(ResourceMetadata::class)
        ->and($data->metadata->name)->toBe('cluster-config')
        ->and($data->metadata->namespace)->toBe('kuven-test-ns')
        ->and($data->metadata->uid)->toBeString()
        ->and(mb_strlen((string) $data->metadata->uid))->toBeGreaterThan(0);
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
    $manifest = new NamespaceManifest(
        metadata: new ManifestMetadata(name: 'my-namespace'),
    );

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

it('resolves the correct endpoint for typed namespaced deployment manifests', function (): void {
    $manifest = new DeploymentManifest(
        metadata: new ManifestMetadata(
            name: 'kuven-api',
            namespace: 'kuven-test-ns',
        ),
        spec: new DeploymentSpec(
            replicas: 1,
            selector: new LabelSelector(
                matchLabels: LabelSet::kuvenApp('kuven-api', 'api'),
            ),
            template: new PodTemplateSpec(
                metadata: new ManifestMetadata(
                    name: 'kuven-api',
                    labels: LabelSet::kuvenApp('kuven-api', 'api'),
                ),
                spec: new PodSpec(
                    containers: [
                        new ContainerSpec(
                            name: 'api',
                            image: 'ghcr.io/getkuven/api:1.2.3',
                        ),
                    ],
                ),
            ),
        ),
    );

    $request = new ApplyManifest($manifest);

    expect($request->resolveEndpoint())->toBe('/apis/apps/v1/namespaces/kuven-test-ns/deployments');
});

it('applies a typed manifest to the kubernetes cluster', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        ApplyManifest::class => MockResponse::fixture('kubernetes/apply-manifest'),
    ]);

    $connector->withMockClient($mockClient);

    $manifest = new DeploymentManifest(
        metadata: new ManifestMetadata(
            name: 'kuven-api',
            namespace: 'kuven-test-ns',
            labels: LabelSet::kuvenApp('kuven-api', 'api'),
        ),
        spec: new DeploymentSpec(
            replicas: 1,
            selector: new LabelSelector(
                matchLabels: LabelSet::kuvenApp('kuven-api', 'api'),
            ),
            template: new PodTemplateSpec(
                metadata: new ManifestMetadata(
                    name: 'kuven-api',
                    labels: LabelSet::kuvenApp('kuven-api', 'api'),
                ),
                spec: new PodSpec(
                    containers: [
                        new ContainerSpec(
                            name: 'api',
                            image: 'ghcr.io/getkuven/api:1.2.3',
                        ),
                    ],
                ),
            ),
        ),
    );

    $response = $connector->send(new ApplyManifest($manifest));

    expect($response->dtoOrFail())
        ->toBeInstanceOf(ManifestData::class);

    $mockClient->assertSent(fn ($currentRequest, $currentResponse): bool => $currentRequest instanceof ApplyManifest
        && $currentResponse->getPendingRequest()->body()?->all() === $manifest->toArray());
});

it('resolves cluster-scoped endpoint for raw namespace array', function (): void {
    $manifest = [
        'apiVersion' => 'v1',
        'kind' => 'Namespace',
        'metadata' => ['name' => 'kuven-test-ns'],
    ];

    $request = new ApplyManifest($manifest);

    expect($request->resolveEndpoint())->toBe('/api/v1/namespaces');
});

it('resolves cluster-scoped endpoint for raw array without namespace', function (): void {
    $manifest = [
        'apiVersion' => 'v1',
        'kind' => 'CustomKind',
        'metadata' => ['name' => 'my-resource'],
    ];

    $request = new ApplyManifest($manifest);

    expect($request->resolveEndpoint())->toBe('/api/v1/customkinds');
});
