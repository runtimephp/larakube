<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\ManifestService;
use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Data\ManifestData;
use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use Illuminate\Support\Str;

final class InMemoryManifestService implements ManifestService
{
    /** @var list<ManifestContract> */
    private array $applied = [];

    public function apply(ManifestContract $manifest): ManifestData
    {
        $this->applied[] = $manifest;

        return new ManifestData(
            apiVersion: $manifest->apiVersion(),
            kind: $manifest->kind(),
            metadata: new ResourceMetadata(
                name: $manifest->toArray()['metadata']['name'],
                uid: Str::uuid()->toString(),
                resourceVersion: (string) random_int(1, 99999),
                creationTimestamp: now()->toImmutable(),
            ),
        );
    }

    /** @return list<ManifestContract> */
    public function applied(): array
    {
        return $this->applied;
    }
}
