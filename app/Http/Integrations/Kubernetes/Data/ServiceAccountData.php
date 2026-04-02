<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

final readonly class ServiceAccountData
{
    public function __construct(
        public ResourceMetadata $metadata,
    ) {}

    /**
     * @param  array<string, mixed>  $response
     */
    public static function fromKubernetesResponse(array $response): self
    {
        return new self(
            metadata: ResourceMetadata::fromKubernetesResponse($response['metadata']),
        );
    }
}
