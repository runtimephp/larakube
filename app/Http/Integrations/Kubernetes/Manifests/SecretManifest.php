<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Enums\SecretType;
use InvalidArgumentException;

final readonly class SecretManifest implements ManifestContract
{
    public function __construct(
        public ManifestMetadata $metadata,
        public SecretStringData $data,
        public SecretType|string $type = SecretType::Opaque,
    ) {
        if ($this->metadata->namespace === null || $this->metadata->namespace === '') {
            throw new InvalidArgumentException('Secret manifests require a namespace.');
        }
    }

    public function apiVersion(): ApiVersion
    {
        return ApiVersion::V1;
    }

    public function kind(): Kind
    {
        return Kind::Secret;
    }

    public function resource(): string
    {
        return 'secrets';
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
     * @return array{apiVersion: string, kind: string, metadata: array{name: string, namespace?: string, labels?: array<string, string>, annotations?: array<string, string>}, type: string, data: array<string, string>}
     */
    public function toArray(): array
    {
        return [
            'apiVersion' => $this->apiVersion()->value,
            'kind' => $this->kind()->value,
            'metadata' => $this->metadata->toArray(),
            'type' => $this->type instanceof SecretType ? $this->type->value : $this->type,
            'data' => $this->data->toEncodedArray(),
        ];
    }
}
