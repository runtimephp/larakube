<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\ServiceAccountService;
use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\Data\ServiceAccountData;
use Illuminate\Support\Str;

final class InMemoryServiceAccountService implements ServiceAccountService
{
    /** @var list<array{name: string, namespace: string}> */
    private array $accounts = [];

    public function create(string $name, string $namespace): ServiceAccountData
    {
        $this->accounts[] = ['name' => $name, 'namespace' => $namespace];

        return new ServiceAccountData(
            metadata: new ResourceMetadata(
                name: $name,
                uid: Str::uuid()->toString(),
                resourceVersion: (string) random_int(1, 99999),
                creationTimestamp: now()->toImmutable(),
                namespace: $namespace,
            ),
        );
    }

    /** @return list<array{name: string, namespace: string}> */
    public function accounts(): array
    {
        return $this->accounts;
    }
}
