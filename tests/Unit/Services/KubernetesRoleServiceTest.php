<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\RoleData;
use App\Http\Integrations\Kubernetes\Data\RuleData;
use App\Http\Integrations\Kubernetes\Enums\RbacApiGroup;
use App\Http\Integrations\Kubernetes\Enums\RbacResource;
use App\Http\Integrations\Kubernetes\Enums\RbacVerb;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateRole;
use App\Services\KubernetesRoleService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function (): void {
    $this->connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $this->service = new KubernetesRoleService($this->connector);
});

test('creates a role and returns role data',
    /**
     * @throws Throwable
     */
    function (): void {
        $mockClient = new MockClient([
            CreateRole::class => MockResponse::fixture('kubernetes/create-role'),
        ]);

        $this->connector->withMockClient($mockClient);

        $result = $this->service->create('kuven-operator', 'kuven-org-123', [
            new RuleData(apiGroups: [RbacApiGroup::CapiCore], resources: [RbacResource::All], verbs: [RbacVerb::All]),
        ]);

        expect($result)->toBeInstanceOf(RoleData::class);
        $mockClient->assertSent(CreateRole::class);
    });
