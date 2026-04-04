<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Http\Integrations\Kubernetes\Data\RoleBindingData;

interface RoleBindingService
{
    public function create(string $name, string $namespace, string $roleName, string $serviceAccountName): RoleBindingData;
}
