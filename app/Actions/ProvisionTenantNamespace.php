<?php

declare(strict_types=1);

namespace App\Actions;

use App\Http\Integrations\Kubernetes\Data\RuleData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateNamespace;
use App\Http\Integrations\Kubernetes\Requests\CreateRole;
use App\Http\Integrations\Kubernetes\Requests\CreateRoleBinding;
use App\Http\Integrations\Kubernetes\Requests\CreateServiceAccount;
use App\Models\Organization;

final class ProvisionTenantNamespace
{
    private const SERVICE_ACCOUNT_NAME = 'kuven-operator';

    private const ROLE_NAME = 'kuven-operator';

    public function handle(KubernetesConnector $connector, Organization $organization): void
    {
        $namespace = "kuven-org-{$organization->id}";

        $connector->send(new CreateNamespace($namespace));

        $connector->send(new CreateServiceAccount(
            name: self::SERVICE_ACCOUNT_NAME,
            namespace: $namespace,
        ));

        $connector->send(new CreateRole(
            name: self::ROLE_NAME,
            namespace: $namespace,
            rules: $this->capiRules(),
        ));

        $connector->send(new CreateRoleBinding(
            name: self::ROLE_NAME,
            namespace: $namespace,
            roleName: self::ROLE_NAME,
            serviceAccountName: self::SERVICE_ACCOUNT_NAME,
        ));
    }

    /**
     * @return list<RuleData>
     */
    private function capiRules(): array
    {
        return [
            new RuleData(
                apiGroups: ['cluster.x-k8s.io', 'infrastructure.cluster.x-k8s.io', 'bootstrap.cluster.x-k8s.io', 'controlplane.cluster.x-k8s.io'],
                resources: ['*'],
                verbs: ['*'],
            ),
            new RuleData(
                apiGroups: [''],
                resources: ['secrets', 'configmaps'],
                verbs: ['*'],
            ),
        ];
    }
}
