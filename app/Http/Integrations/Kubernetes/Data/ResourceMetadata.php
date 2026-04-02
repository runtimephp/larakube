<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

use Carbon\CarbonImmutable;

final readonly class ResourceMetadata
{
    /**
     * @param  array<string, string>  $labels
     * @param  array<string, string>  $annotations
     */
    public function __construct(
        public string $name,
        public string $uid,
        public string $resourceVersion,
        public CarbonImmutable $creationTimestamp,
        public ?string $namespace = null,
        public array $labels = [],
        public array $annotations = [],
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public static function fromKubernetesResponse(array $metadata): self
    {
        return new self(
            name: $metadata['name'],
            uid: $metadata['uid'],
            resourceVersion: $metadata['resourceVersion'],
            creationTimestamp: CarbonImmutable::parse($metadata['creationTimestamp']),
            namespace: $metadata['namespace'] ?? null,
            labels: $metadata['labels'] ?? [],
            annotations: $metadata['annotations'] ?? [],
        );
    }
}
