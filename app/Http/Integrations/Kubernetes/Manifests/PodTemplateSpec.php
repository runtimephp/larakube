<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

final readonly class PodTemplateSpec
{
    public function __construct(
        public ManifestMetadata $metadata,
        public PodSpec $spec,
    ) {}

    /**
     * @return array{metadata: array{name: string, namespace?: string, labels?: array<string, string>, annotations?: array<string, string>}, spec: array{containers: list<array{name: string, image: string, ports?: list<array{containerPort: int, protocol: string, name?: string}>, env?: list<array{name: string, value: string}>}>, serviceAccountName?: string}}
     */
    public function toArray(): array
    {
        return [
            'metadata' => $this->metadata->toArray(),
            'spec' => $this->spec->toArray(),
        ];
    }
}
