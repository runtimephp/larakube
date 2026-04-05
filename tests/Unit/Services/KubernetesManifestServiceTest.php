<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\ManifestData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Docker\DockerClusterManifest;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Requests\ApplyManifest;
use App\Services\KubernetesManifestService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function (): void {
    $this->connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $this->service = new KubernetesManifestService($this->connector);
});

test('applies a manifest and returns manifest data',
    /**
     * @throws Throwable
     */
    function (): void {
        $mockClient = new MockClient([
            ApplyManifest::class => MockResponse::fixture('kubernetes/apply-manifest'),
        ]);

        $this->connector->withMockClient($mockClient);

        $manifest = new DockerClusterManifest(
            metadata: new ManifestMetadata(name: 'my-cluster', namespace: 'kuven-org-123'),
        );

        $result = $this->service->apply($manifest);

        expect($result)->toBeInstanceOf(ManifestData::class);
        $mockClient->assertSent(ApplyManifest::class);
    });
