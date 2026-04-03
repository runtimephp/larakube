<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use InvalidArgumentException;

final readonly class DeploymentManifest implements ManifestContract
{
    public function __construct(
        public ManifestMetadata $metadata,
        public DeploymentSpec $spec,
    ) {
        if ($this->metadata->namespace === null || $this->metadata->namespace === '') {
            throw new InvalidArgumentException('Deployment manifests require a namespace.');
        }
    }

    public function apiVersion(): ApiVersion
    {
        return ApiVersion::AppsV1;
    }

    public function kind(): Kind
    {
        return Kind::Deployment;
    }

    public function resource(): string
    {
        return 'deployments';
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
     * @return array{apiVersion: string, kind: string, metadata: array{name: string, namespace: string, labels?: array<string, string>, annotations?: array<string, string>}, spec: array{replicas: int, selector: array{matchLabels: array<string, string>}, template: array{metadata: array{name: string, namespace?: string, labels?: array<string, string>, annotations?: array<string, string>}, spec: array{containers: list<array{name: string, image: string, ports?: list<array{containerPort: int, protocol: string, name?: string}>, env?: list<array{name: string, value: string}>}>, serviceAccountName?: string}}}}
     */
    public function toArray(): array
    {
        return [
            'apiVersion' => $this->apiVersion()->value,
            'kind' => $this->kind()->value,
            'metadata' => $this->metadata->toArray(),
            'spec' => $this->spec->toArray(),
        ];
    }
}
