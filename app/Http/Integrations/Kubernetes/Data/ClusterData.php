<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

final readonly class ClusterData
{
    /**
     * @param  list<ConditionData>  $conditions
     */
    public function __construct(
        public ResourceMetadata $metadata,
        public string $phase,
        public array $conditions = [],
    ) {}

    /**
     * @param  array{metadata: array{name: string, uid: string, resourceVersion: string, creationTimestamp: string, namespace?: string, labels?: array<string, string>, annotations?: array<string, string>}, status?: array{phase?: string, conditions?: list<array{type: string, status: string, reason?: string, message?: string, lastTransitionTime?: string}>}}  $response
     */
    public static function fromKubernetesResponse(array $response): self
    {
        $status = $response['status'] ?? [];

        return new self(
            metadata: ResourceMetadata::fromKubernetesResponse($response['metadata']),
            phase: $status['phase'] ?? 'Pending',
            conditions: array_map(
                ConditionData::fromKubernetesResponse(...),
                $status['conditions'] ?? [],
            ),
        );
    }

    public function isReady(): bool
    {
        foreach ($this->conditions as $condition) {
            if ($condition->type === 'Ready') {
                return $condition->isTrue();
            }
        }

        return false;
    }
}
