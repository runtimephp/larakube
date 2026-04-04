<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\NamespaceService;
use App\Contracts\NetworkPolicyService;
use App\Contracts\ResourceQuotaService;
use App\Contracts\RoleBindingService;
use App\Contracts\RoleService;
use App\Contracts\ServiceAccountService;
use App\Data\TenantQuotaData;
use App\Http\Integrations\Kubernetes\Enums\KuvenResource;
use App\Models\Organization;

final readonly class ProvisionTenantNamespace
{
    public function __construct(
        private NamespaceService $namespaceService,
        private ServiceAccountService $serviceAccountService,
        private RoleService $roleService,
        private RoleBindingService $roleBindingService,
        private NetworkPolicyService $networkPolicyService,
        private ResourceQuotaService $resourceQuotaService,
        private BuildCapiRbacRules $buildCapiRbacRules,
    ) {}

    public function handle(Organization $organization): void
    {
        $namespace = "kuven-org-{$organization->id}";

        $this->namespaceService->create($namespace);

        $this->serviceAccountService->create(KuvenResource::Operator->value, $namespace);

        $this->roleService->create(KuvenResource::Operator->value, $namespace, $this->buildCapiRbacRules->handle());

        $this->roleBindingService->create(
            KuvenResource::Operator->value,
            $namespace,
            KuvenResource::Operator->value,
            KuvenResource::Operator->value,
        );

        $this->networkPolicyService->applyDefaultDeny(KuvenResource::DefaultDenyPolicy->value, $namespace);

        $this->resourceQuotaService->apply(KuvenResource::TenantQuota->value, $namespace, new TenantQuotaData);
    }
}
