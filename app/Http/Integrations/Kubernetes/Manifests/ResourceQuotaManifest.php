<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use InvalidArgumentException;

final readonly class ResourceQuotaManifest implements ManifestContract
{
    /**
     * @param  array<string, string>  $hard
     */
    public function __construct(
        public ManifestMetadata $metadata,
        public array $hard,
    ) {
        if ($this->metadata->namespace === null || mb_trim($this->metadata->namespace) === '') {
            throw new InvalidArgumentException('ResourceQuota manifests require a namespace.');
        }
    }

    public function apiVersion(): ApiVersion
    {
        return ApiVersion::V1;
    }

    public function kind(): Kind
    {
        return Kind::ResourceQuota;
    }

    public function resource(): string
    {
        return 'resourcequotas';
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
     * @return array{apiVersion: string, kind: string, metadata: array{name: string, namespace: string, labels?: array<string, string>, annotations?: array<string, string>}, spec: array{hard: array<string, string>}}
     */
    public function toArray(): array
    {
        return [
            'apiVersion' => $this->apiVersion()->value,
            'kind' => $this->kind()->value,
            'metadata' => $this->metadata->toArray(),
            'spec' => [
                'hard' => $this->hard,
            ],
        ];
    }
}
