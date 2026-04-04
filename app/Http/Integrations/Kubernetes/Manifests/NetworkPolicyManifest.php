<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use InvalidArgumentException;

final readonly class NetworkPolicyManifest implements ManifestContract
{
    public function __construct(
        public ManifestMetadata $metadata,
    ) {
        if ($this->metadata->namespace === null || mb_trim($this->metadata->namespace) === '') {
            throw new InvalidArgumentException('NetworkPolicy manifests require a namespace.');
        }
    }

    public function apiVersion(): ApiVersion
    {
        return ApiVersion::NetworkingV1;
    }

    public function kind(): Kind
    {
        return Kind::NetworkPolicy;
    }

    public function resource(): string
    {
        return 'networkpolicies';
    }

    public function namespace(): string
    {
        return $this->metadata->namespace;
    }

    public function isClusterScoped(): bool
    {
        return false;
    }

    /**
     * @return array{apiVersion: string, kind: string, metadata: array{name: string, namespace: string, labels?: array<string, string>, annotations?: array<string, string>}, spec: array{podSelector: object, policyTypes: list<string>}}
     */
    public function toArray(): array
    {
        return [
            'apiVersion' => $this->apiVersion()->value,
            'kind' => $this->kind()->value,
            'metadata' => $this->metadata->toArray(),
            'spec' => [
                'podSelector' => (object) [],
                'policyTypes' => ['Ingress', 'Egress'],
            ],
        ];
    }
}
