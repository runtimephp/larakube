<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

final readonly class ServiceAccountData
{
    public function __construct(
        public ResourceMetadata $metadata,
    ) {}

    /**
     * @param  array{metadata: array{name: string, uid: string, resourceVersion: string, creationTimestamp: string, namespace?: string, labels?: array<string, string>, annotations?: array<string, string>}}  $response
     */
    public static function fromKubernetesResponse(array $response): self
    {
        return new self(
            metadata: ResourceMetadata::fromKubernetesResponse($response['metadata']),
        );
    }
}
