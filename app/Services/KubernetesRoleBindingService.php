<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RoleBindingService;
use App\Http\Integrations\Kubernetes\Data\RoleBindingData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateRoleBinding;

final readonly class KubernetesRoleBindingService implements RoleBindingService
{
    public function __construct(
        private KubernetesConnector $connector,
    ) {}

    public function create(string $name, string $namespace, string $roleName, string $serviceAccountName): RoleBindingData
    {
        return $this->connector->send(new CreateRoleBinding($name, $namespace, $roleName, $serviceAccountName))->dtoOrFail();
    }
}
