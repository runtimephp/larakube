<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\RoleService;
use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\Data\RoleData;
use Illuminate\Support\Str;

final class InMemoryRoleService implements RoleService
{
    /** @var list<array{name: string, namespace: string}> */
    private array $roles = [];

    public function create(string $name, string $namespace, array $rules): RoleData
    {
        $this->roles[] = ['name' => $name, 'namespace' => $namespace];

        return new RoleData(
            metadata: new ResourceMetadata(
                name: $name,
                uid: Str::uuid()->toString(),
                resourceVersion: (string) random_int(1, 99999),
                creationTimestamp: now()->toImmutable(),
                namespace: $namespace,
            ),
            rules: $rules,
        );
    }

    /** @return list<array{name: string, namespace: string}> */
    public function roles(): array
    {
        return $this->roles;
    }
}
