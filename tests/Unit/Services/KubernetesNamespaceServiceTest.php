<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\NamespaceData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateNamespace;
use App\Services\KubernetesNamespaceService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function (): void {
    $this->connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $this->service = new KubernetesNamespaceService($this->connector);
});

test('creates a namespace and returns namespace data',
    /**
     * @throws Throwable
     */
    function (): void {
        $mockClient = new MockClient([
            CreateNamespace::class => MockResponse::fixture('kubernetes/create-namespace'),
        ]);

        $this->connector->withMockClient($mockClient);

        $result = $this->service->create('kuven-org-123');

        expect($result)->toBeInstanceOf(NamespaceData::class);
        $mockClient->assertSent(CreateNamespace::class);
    });
