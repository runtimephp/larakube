<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests\Capi;

use App\Http\Integrations\Kubernetes\Manifests\ObjectReference;

final readonly class ClusterSpec
{
    public function __construct(
        public ObjectReference $controlPlaneRef,
        public ObjectReference $infrastructureRef,
        public ClusterNetworkSpec $clusterNetwork = new ClusterNetworkSpec,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'clusterNetwork' => $this->clusterNetwork->toArray(),
            'controlPlaneRef' => $this->controlPlaneRef->toArray(),
            'infrastructureRef' => $this->infrastructureRef->toArray(),
        ];
    }
}
