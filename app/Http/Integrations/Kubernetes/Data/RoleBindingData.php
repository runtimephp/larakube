<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

final readonly class RoleBindingData
{
    /**
     * @param  list<array<string, string>>  $subjects
     */
    public function __construct(
        public ResourceMetadata $metadata,
        public string $roleName,
        public array $subjects,
    ) {}

    /**
     * @param  array<string, mixed>  $response
     */
    public static function fromKubernetesResponse(array $response): self
    {
        return new self(
            metadata: ResourceMetadata::fromKubernetesResponse($response['metadata']),
            roleName: $response['roleRef']['name'],
            subjects: $response['subjects'] ?? [],
        );
    }
}
