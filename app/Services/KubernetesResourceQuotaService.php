<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ResourceQuotaService;
use App\Data\TenantQuotaData;
use App\Http\Integrations\Kubernetes\Data\ManifestData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\ResourceQuotaManifest;
use App\Http\Integrations\Kubernetes\Requests\ApplyManifest;

final readonly class KubernetesResourceQuotaService implements ResourceQuotaService
{
    public function __construct(
        private KubernetesConnector $connector,
    ) {}

    public function apply(string $name, string $namespace, TenantQuotaData $tenantQuotaData): ManifestData
    {
        return $this->connector->send(new ApplyManifest(new ResourceQuotaManifest(
            metadata: new ManifestMetadata(
                name: $name,
                namespace: $namespace,
            ),
            hard: $tenantQuotaData->toKubernetesHard(),
        )))->dtoOrFail();
    }
}
