<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

final readonly class RuleData
{
    /**
     * @param  list<string>  $apiGroups
     * @param  list<string>  $resources
     * @param  list<string>  $verbs
     */
    public function __construct(
        public array $apiGroups,
        public array $resources,
        public array $verbs,
    ) {}

    /**
     * @param  array<string, mixed>  $rule
     */
    public static function fromKubernetesResponse(array $rule): self
    {
        return new self(
            apiGroups: $rule['apiGroups'] ?? [],
            resources: $rule['resources'] ?? [],
            verbs: $rule['verbs'] ?? [],
        );
    }

    /**
     * @return array<string, list<string>>
     */
    public function toArray(): array
    {
        return [
            'apiGroups' => $this->apiGroups,
            'resources' => $this->resources,
            'verbs' => $this->verbs,
        ];
    }
}
