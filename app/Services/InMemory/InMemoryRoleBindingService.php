<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\RoleBindingService;
use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\Data\RoleBindingData;
use Illuminate\Support\Str;

final class InMemoryRoleBindingService implements RoleBindingService
{
    /** @var list<array{name: string, namespace: string}> */
    private array $bindings = [];

    public function create(string $name, string $namespace, string $roleName, string $serviceAccountName): RoleBindingData
    {
        $this->bindings[] = ['name' => $name, 'namespace' => $namespace];

        return new RoleBindingData(
            metadata: new ResourceMetadata(
                name: $name,
                uid: Str::uuid()->toString(),
                resourceVersion: (string) random_int(1, 99999),
                creationTimestamp: now()->toImmutable(),
                namespace: $namespace,
            ),
            roleName: $roleName,
            subjects: [
                [
                    'kind' => 'ServiceAccount',
                    'name' => $serviceAccountName,
                    'namespace' => $namespace,
                ],
            ],
        );
    }

    /** @return list<array{name: string, namespace: string}> */
    public function bindings(): array
    {
        return $this->bindings;
    }
}
