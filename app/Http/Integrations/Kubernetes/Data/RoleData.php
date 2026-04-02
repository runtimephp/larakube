<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

final readonly class RoleData
{
    /**
     * @param  list<RuleData>  $rules
     */
    public function __construct(
        public ResourceMetadata $metadata,
        public array $rules,
    ) {}

    /**
     * @param  array{metadata: array{name: string, uid: string, resourceVersion: string, creationTimestamp: string, namespace?: string, labels?: array<string, string>, annotations?: array<string, string>}, rules?: list<array{apiGroups?: list<string>, resources?: list<string>, verbs?: list<string>}>}  $response
     */
    public static function fromKubernetesResponse(array $response): self
    {
        return new self(
            metadata: ResourceMetadata::fromKubernetesResponse($response['metadata']),
            rules: array_map(
                RuleData::fromKubernetesResponse(...),
                $response['rules'] ?? [],
            ),
        );
    }
}
