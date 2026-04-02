<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;

final readonly class ServiceAccountManifest implements ManifestContract
{
    public function __construct(
        public ManifestMetadata $metadata,
    ) {}

    public function apiVersion(): ApiVersion
    {
        return ApiVersion::V1;
    }

    public function kind(): Kind
    {
        return Kind::ServiceAccount;
    }

    public function resource(): string
    {
        return 'serviceaccounts';
    }

    public function namespace(): ?string
    {
        return $this->metadata->namespace;
    }

    public function isClusterScoped(): bool
    {
        return false;
    }

    /**
     * @return array{apiVersion: string, kind: string, metadata: array{name: string, namespace?: string, labels?: array<string, string>, annotations?: array<string, string>}}
     */
    public function toArray(): array
    {
        return [
            'apiVersion' => $this->apiVersion()->value,
            'kind' => $this->kind()->value,
            'metadata' => $this->metadata->toArray(),
        ];
    }
}
