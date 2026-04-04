<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests\Capi;

use App\Http\Integrations\Kubernetes\Manifests\ObjectReference;

final readonly class MachineDeploymentSpec
{
    public function __construct(
        public string $clusterName,
        public int $replicas,
        public string $version,
        public ObjectReference $bootstrapConfigRef,
        public ObjectReference $infrastructureRef,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'clusterName' => $this->clusterName,
            'replicas' => $this->replicas,
            'selector' => [
                'matchLabels' => (object) [],
            ],
            'template' => [
                'spec' => [
                    'version' => $this->version,
                    'clusterName' => $this->clusterName,
                    'bootstrap' => [
                        'configRef' => $this->bootstrapConfigRef->toArray(),
                    ],
                    'infrastructureRef' => $this->infrastructureRef->toArray(),
                ],
            ],
        ];
    }
}
