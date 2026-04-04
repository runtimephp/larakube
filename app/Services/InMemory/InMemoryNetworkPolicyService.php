<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\NetworkPolicyService;
use App\Http\Integrations\Kubernetes\Data\ManifestData;
use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use Illuminate\Support\Str;

final class InMemoryNetworkPolicyService implements NetworkPolicyService
{
    /** @var list<array{name: string, namespace: string}> */
    private array $policies = [];

    public function applyDefaultDeny(string $name, string $namespace): ManifestData
    {
        $this->policies[] = ['name' => $name, 'namespace' => $namespace];

        return new ManifestData(
            apiVersion: ApiVersion::NetworkingV1,
            kind: Kind::NetworkPolicy,
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
    public function policies(): array
    {
        return $this->policies;
    }
}
