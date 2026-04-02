<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

final readonly class RoleBindingData
{
    /**
     * @param  list<array{kind: string, name: string, namespace?: string, apiGroup?: string}>  $subjects
     */
    public function __construct(
        public ResourceMetadata $metadata,
        public string $roleName,
        public array $subjects,
    ) {}

    /**
     * @param  array{metadata: array{name: string, uid: string, resourceVersion: string, creationTimestamp: string, namespace?: string, labels?: array<string, string>, annotations?: array<string, string>}, roleRef: array{apiGroup: string, kind: string, name: string}, subjects?: list<array{kind: string, name: string, namespace?: string, apiGroup?: string}>}  $response
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
