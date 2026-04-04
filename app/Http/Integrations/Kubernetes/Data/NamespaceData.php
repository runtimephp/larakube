<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

use App\Http\Integrations\Kubernetes\Enums\NamespacePhase;

final readonly class NamespaceData
{
    public function __construct(
        public ResourceMetadata $metadata,
        public NamespacePhase $phase,
    ) {}

    /**
     * @param  array{metadata: array{name: string, uid: string, resourceVersion: string, creationTimestamp: string, namespace?: string, labels?: array<string, string>, annotations?: array<string, string>}, status?: array{phase?: string}}  $response
     */
    public static function fromKubernetesResponse(array $response): self
    {
        return new self(
            metadata: ResourceMetadata::fromKubernetesResponse($response['metadata']),
            phase: NamespacePhase::from($response['status']['phase'] ?? 'Active'),
        );
    }
}
