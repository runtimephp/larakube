<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\NetworkPolicyService;
use App\Http\Integrations\Kubernetes\Data\ManifestData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\NetworkPolicyManifest;
use App\Http\Integrations\Kubernetes\Requests\ApplyManifest;

final readonly class KubernetesNetworkPolicyService implements NetworkPolicyService
{
    public function __construct(
        private KubernetesConnector $connector,
    ) {}

    public function applyDefaultDeny(string $name, string $namespace): ManifestData
    {
        return $this->connector->send(new ApplyManifest(new NetworkPolicyManifest(
            metadata: new ManifestMetadata(
                name: $name,
                namespace: $namespace,
            ),
        )))->dtoOrFail();
    }
}
