<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

use App\Http\Integrations\Kubernetes\Enums\RbacApiGroup;
use App\Http\Integrations\Kubernetes\Enums\RbacResource;
use App\Http\Integrations\Kubernetes\Enums\RbacVerb;

final readonly class RuleData
{
    /**
     * @param  list<RbacApiGroup>  $apiGroups
     * @param  list<RbacResource>  $resources
     * @param  list<RbacVerb>  $verbs
     */
    public function __construct(
        public array $apiGroups,
        public array $resources,
        public array $verbs,
    ) {}

    /**
     * @param  array{apiGroups?: list<string>, resources?: list<string>, verbs?: list<string>}  $rule
     */
    public static function fromKubernetesResponse(array $rule): self
    {
        return new self(
            apiGroups: array_map(
                RbacApiGroup::from(...),
                $rule['apiGroups'] ?? [],
            ),
            resources: array_map(
                RbacResource::from(...),
                $rule['resources'] ?? [],
            ),
            verbs: array_map(
                RbacVerb::from(...),
                $rule['verbs'] ?? [],
            ),
        );
    }

    /**
     * @return array{apiGroups: list<string>, resources: list<string>, verbs: list<string>}
     */
    public function toArray(): array
    {
        return [
            'apiGroups' => array_map(fn (RbacApiGroup $group): string => $group->value, $this->apiGroups),
            'resources' => array_map(fn (RbacResource $resource): string => $resource->value, $this->resources),
            'verbs' => array_map(fn (RbacVerb $verb): string => $verb->value, $this->verbs),
        ];
    }
}
