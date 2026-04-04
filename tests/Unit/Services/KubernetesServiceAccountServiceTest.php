<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\ServiceAccountData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateServiceAccount;
use App\Services\KubernetesServiceAccountService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function (): void {
    $this->connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $this->service = new KubernetesServiceAccountService($this->connector);
});

test('creates a service account and returns service account data',
    /**
     * @throws Throwable
     */
    function (): void {
        $mockClient = new MockClient([
            CreateServiceAccount::class => MockResponse::fixture('kubernetes/create-service-account'),
        ]);

        $this->connector->withMockClient($mockClient);

        $result = $this->service->create('kuven-operator', 'kuven-org-123');

        expect($result)->toBeInstanceOf(ServiceAccountData::class);
        $mockClient->assertSent(CreateServiceAccount::class);
    });
