<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\ResourceQuotaService;
use App\Data\TenantQuotaData;
use App\Http\Integrations\Kubernetes\Data\ManifestData;
use App\Http\Integrations\Kubernetes\Data\ResourceMetadata;
use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use Illuminate\Support\Str;

final class InMemoryResourceQuotaService implements ResourceQuotaService
{
    /** @var list<array{name: string, namespace: string, quota: TenantQuotaData}> */
    private array $quotas = [];

    public function apply(string $name, string $namespace, TenantQuotaData $tenantQuotaData): ManifestData
    {
        $this->quotas[] = ['name' => $name, 'namespace' => $namespace, 'quota' => $tenantQuotaData];

        return new ManifestData(
            apiVersion: ApiVersion::V1,
            kind: Kind::ResourceQuota,
            metadata: new ResourceMetadata(
                name: $name,
                uid: Str::uuid()->toString(),
                resourceVersion: (string) random_int(1, 99999),
                creationTimestamp: now()->toImmutable(),
                namespace: $namespace,
            ),
        );
    }

    /** @return list<array{name: string, namespace: string, quota: TenantQuotaData}> */
    public function quotas(): array
    {
        return $this->quotas;
    }
}
