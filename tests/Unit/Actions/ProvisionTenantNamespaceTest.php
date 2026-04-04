<?php

declare(strict_types=1);

use App\Actions\ProvisionTenantNamespace;
use App\Contracts\NamespaceService;
use App\Contracts\NetworkPolicyService;
use App\Contracts\ResourceQuotaService;
use App\Contracts\RoleBindingService;
use App\Contracts\RoleService;
use App\Contracts\ServiceAccountService;
use App\Models\Organization;
use App\Services\InMemory\InMemoryNamespaceService;
use App\Services\InMemory\InMemoryNetworkPolicyService;
use App\Services\InMemory\InMemoryResourceQuotaService;
use App\Services\InMemory\InMemoryRoleBindingService;
use App\Services\InMemory\InMemoryRoleService;
use App\Services\InMemory\InMemoryServiceAccountService;

beforeEach(function (): void {
    $this->namespaceService = new InMemoryNamespaceService;
    $this->serviceAccountService = new InMemoryServiceAccountService;
    $this->roleService = new InMemoryRoleService;
    $this->roleBindingService = new InMemoryRoleBindingService;
    $this->networkPolicyService = new InMemoryNetworkPolicyService;
    $this->resourceQuotaService = new InMemoryResourceQuotaService;

    $this->app->instance(NamespaceService::class, $this->namespaceService);
    $this->app->instance(ServiceAccountService::class, $this->serviceAccountService);
    $this->app->instance(RoleService::class, $this->roleService);
    $this->app->instance(RoleBindingService::class, $this->roleBindingService);
    $this->app->instance(NetworkPolicyService::class, $this->networkPolicyService);
    $this->app->instance(ResourceQuotaService::class, $this->resourceQuotaService);

    $this->action = $this->app->make(ProvisionTenantNamespace::class);
});

test('provisions all 6 kubernetes resources for a tenant namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        $this->action->handle($organization);

        expect($this->namespaceService->namespaces())->toHaveCount(1)
            ->and($this->serviceAccountService->accounts())->toHaveCount(1)
            ->and($this->roleService->roles())->toHaveCount(1)
            ->and($this->roleBindingService->bindings())->toHaveCount(1)
            ->and($this->networkPolicyService->policies())->toHaveCount(1)
            ->and($this->resourceQuotaService->quotas())->toHaveCount(1);
    });

test('builds namespace name from organization id',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        $this->action->handle($organization);

        expect($this->namespaceService->namespaces()[0])->toBe("kuven-org-{$organization->id}");
    });

test('all resources use the correct namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        $this->action->handle($organization);

        $expectedNamespace = "kuven-org-{$organization->id}";

        expect($this->serviceAccountService->accounts()[0]['namespace'])->toBe($expectedNamespace)
            ->and($this->roleService->roles()[0]['namespace'])->toBe($expectedNamespace)
            ->and($this->roleBindingService->bindings()[0]['namespace'])->toBe($expectedNamespace)
            ->and($this->networkPolicyService->policies()[0]['namespace'])->toBe($expectedNamespace)
            ->and($this->resourceQuotaService->quotas()[0]['namespace'])->toBe($expectedNamespace);
    });
