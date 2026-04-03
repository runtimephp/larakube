<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\ManifestData;
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
use App\Http\Integrations\Kubernetes\Manifests\SecretManifest;
use App\Http\Integrations\Kubernetes\Manifests\SecretStringData;
use App\Http\Integrations\Kubernetes\Requests\ServerSideApply;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

test('resolves correct endpoint for namespaced resource', function (): void {
    $manifest = new SecretManifest(
        metadata: new ManifestMetadata(
            name: 'hetzner-credentials',
            namespace: 'kuven-test-ns',
        ),
        data: new SecretStringData(['token' => 'value']),
    );

    $request = new ServerSideApply($manifest);

    expect($request->resolveEndpoint())->toBe('/api/v1/namespaces/kuven-test-ns/secrets/hetzner-credentials');
});

test('resolves correct endpoint for cluster-scoped resource', function (): void {
    $manifest = new NamespaceManifest(
        metadata: new ManifestMetadata(name: 'kuven-test-ns'),
    );

    $request = new ServerSideApply($manifest);

    expect($request->resolveEndpoint())->toBe('/api/v1/namespaces/kuven-test-ns');
});

test('sets apply patch content type', function (): void {
    $manifest = new SecretManifest(
        metadata: new ManifestMetadata(name: 'creds', namespace: 'default'),
        data: new SecretStringData(['k' => 'v']),
    );

    $request = new ServerSideApply($manifest);
    $headers = $request->headers()->all();

    expect($headers['Content-Type'])->toBe('application/apply-patch+yaml');
});

test('includes fieldManager query parameter', function (): void {
    $manifest = new SecretManifest(
        metadata: new ManifestMetadata(name: 'creds', namespace: 'default'),
        data: new SecretStringData(['k' => 'v']),
    );

    $request = new ServerSideApply($manifest, fieldManager: 'my-controller');
    $query = $request->query()->all();

    expect($query['fieldManager'])->toBe('my-controller');
});

test('includes force query parameter when set', function (): void {
    $manifest = new SecretManifest(
        metadata: new ManifestMetadata(name: 'creds', namespace: 'default'),
        data: new SecretStringData(['k' => 'v']),
    );

    $request = new ServerSideApply($manifest, force: true);
    $query = $request->query()->all();

    expect($query['force'])->toBe('true');
});

test('omits force query parameter when not set', function (): void {
    $manifest = new SecretManifest(
        metadata: new ManifestMetadata(name: 'creds', namespace: 'default'),
        data: new SecretStringData(['k' => 'v']),
    );

    $request = new ServerSideApply($manifest);
    $query = $request->query()->all();

    expect($query)->not->toHaveKey('force');
});

test('resolves correct endpoint for api group resource', function (): void {
    $labels = LabelSet::kuvenApp('kuven-api', 'api');

    $manifest = new DeploymentManifest(
        metadata: new ManifestMetadata(name: 'kuven-api', namespace: 'kuven-ns'),
        spec: new DeploymentSpec(
            replicas: 1,
            selector: new LabelSelector(matchLabels: $labels),
            template: new PodTemplateSpec(
                metadata: new ManifestMetadata(name: 'kuven-api', labels: $labels),
                spec: new PodSpec(containers: [new ContainerSpec(name: 'api', image: 'nginx')]),
            ),
        ),
    );

    $request = new ServerSideApply($manifest);

    expect($request->resolveEndpoint())->toBe('/apis/apps/v1/namespaces/kuven-ns/deployments/kuven-api');
});

test('sends manifest body and returns ManifestData', function (): void {
    $connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $mockClient = new MockClient([
        ServerSideApply::class => MockResponse::fixture('kubernetes/apply-manifest'),
    ]);

    $connector->withMockClient($mockClient);

    $manifest = new SecretManifest(
        metadata: new ManifestMetadata(name: 'creds', namespace: 'default'),
        data: new SecretStringData(['token' => 'val']),
    );

    $response = $connector->send(new ServerSideApply($manifest));

    expect($response->dtoOrFail())->toBeInstanceOf(ManifestData::class);

    $mockClient->assertSent(fn ($req, $res): bool => $req instanceof ServerSideApply
        && $res->getPendingRequest()->body()?->all() === $manifest->toArray());
});
