<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\RoleBindingData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateRoleBinding;
use App\Services\KubernetesRoleBindingService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function (): void {
    $this->connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $this->service = new KubernetesRoleBindingService($this->connector);
});

test('creates a role binding and returns role binding data',
    /**
     * @throws Throwable
     */
    function (): void {
        $mockClient = new MockClient([
            CreateRoleBinding::class => MockResponse::fixture('kubernetes/create-role-binding'),
        ]);

        $this->connector->withMockClient($mockClient);

        $result = $this->service->create('kuven-operator', 'kuven-org-123', 'kuven-operator', 'kuven-operator');

        expect($result)->toBeInstanceOf(RoleBindingData::class);
        $mockClient->assertSent(CreateRoleBinding::class);
    });
