<?php

declare(strict_types=1);

use App\Actions\ProvisionTenantNamespace;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateNamespace;
use App\Http\Integrations\Kubernetes\Requests\CreateRole;
use App\Http\Integrations\Kubernetes\Requests\CreateRoleBinding;
use App\Http\Integrations\Kubernetes\Requests\CreateServiceAccount;
use App\Models\Organization;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function (): void {
    $this->connector = new KubernetesConnector(
        server: 'https://127.0.0.1:60517',
        token: 'test-token',
        verifySsl: false,
    );

    $this->action = app(ProvisionTenantNamespace::class);
});

test('provisions namespace, service account, role, and role binding',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        $mockClient = new MockClient([
            CreateNamespace::class => MockResponse::fixture('kubernetes/create-namespace'),
            CreateServiceAccount::class => MockResponse::fixture('kubernetes/create-service-account'),
            CreateRole::class => MockResponse::fixture('kubernetes/create-role'),
            CreateRoleBinding::class => MockResponse::fixture('kubernetes/create-role-binding'),
        ]);

        $this->connector->withMockClient($mockClient);

        $this->action->handle($this->connector, $organization);

        $mockClient->assertSentCount(4);
        $mockClient->assertSent(CreateNamespace::class);
        $mockClient->assertSent(CreateServiceAccount::class);
        $mockClient->assertSent(CreateRole::class);
        $mockClient->assertSent(CreateRoleBinding::class);
    });

test('builds namespace name from organization id',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        $mockClient = new MockClient([
            CreateNamespace::class => MockResponse::fixture('kubernetes/create-namespace'),
            CreateServiceAccount::class => MockResponse::fixture('kubernetes/create-service-account'),
            CreateRole::class => MockResponse::fixture('kubernetes/create-role'),
            CreateRoleBinding::class => MockResponse::fixture('kubernetes/create-role-binding'),
        ]);

        $this->connector->withMockClient($mockClient);

        $this->action->handle($this->connector, $organization);

        $mockClient->assertSent(CreateNamespace::class);
    });
