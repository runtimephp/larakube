<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RoleService;
use App\Http\Integrations\Kubernetes\Data\RoleData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\CreateRole;

final readonly class KubernetesRoleService implements RoleService
{
    public function __construct(
        private KubernetesConnector $connector,
    ) {}

    public function create(string $name, string $namespace, array $rules): RoleData
    {
        return $this->connector->send(new CreateRole($name, $namespace, $rules))->dtoOrFail();
    }
}
