<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Http\Integrations\Kubernetes\Data\RoleData;
use App\Http\Integrations\Kubernetes\Data\RuleData;

interface RoleService
{
    /**
     * @param  list<RuleData>  $rules
     */
    public function create(string $name, string $namespace, array $rules): RoleData;
}
