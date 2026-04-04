<?php

declare(strict_types=1);

use App\Data\TenantQuotaData;
use App\Http\Integrations\Kubernetes\Data\ManifestData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\ApplyManifest;
use App\Services\KubernetesResourceQuotaService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function (): void {
    $this->connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $this->service = new KubernetesResourceQuotaService($this->connector);
});

test('applies a resource quota and returns manifest data',
    /**
     * @throws Throwable
     */
    function (): void {
        $mockClient = new MockClient([
            ApplyManifest::class => MockResponse::fixture('kubernetes/apply-manifest'),
        ]);

        $this->connector->withMockClient($mockClient);

        $result = $this->service->apply('tenant-quota', 'kuven-org-123', new TenantQuotaData);

        expect($result)->toBeInstanceOf(ManifestData::class);
        $mockClient->assertSent(ApplyManifest::class);
    });
