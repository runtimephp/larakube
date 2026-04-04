<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\NamespaceService;
use App\Http\Integrations\Kubernetes\Data\NamespaceData;
use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\Enums\NamespacePhase;
use Illuminate\Support\Str;

final class InMemoryNamespaceService implements NamespaceService
{
    /** @var list<string> */
    private array $namespaces = [];

    public function create(string $name): NamespaceData
    {
        $this->namespaces[] = $name;

        return new NamespaceData(
            metadata: new ResourceMetadata(
                name: $name,
                uid: Str::uuid()->toString(),
                resourceVersion: (string) random_int(1, 99999),
                creationTimestamp: now()->toImmutable(),
            ),
            phase: NamespacePhase::Active,
        );
    }

    /** @return list<string> */
    public function namespaces(): array
    {
        return $this->namespaces;
    }
}
