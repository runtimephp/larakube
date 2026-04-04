<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;

final readonly class ManifestData
{
    /**
     * @param  array<string, mixed>  $spec
     * @param  array<string, mixed>  $status
     */
    public function __construct(
        public ApiVersion $apiVersion,
        public Kind $kind,
        public ResourceMetadata $metadata,
        public array $spec = [],
        public array $status = [],
    ) {}

    /**
     * @param  array{apiVersion: string, kind: string, metadata: array{name: string, uid: string, resourceVersion: string, creationTimestamp: string, namespace?: string, labels?: array<string, string>, annotations?: array<string, string>}, spec?: array<string, mixed>, status?: array<string, mixed>}  $response
     */
    public static function fromKubernetesResponse(array $response): self
    {
        return new self(
            apiVersion: ApiVersion::from($response['apiVersion']),
            kind: Kind::from($response['kind']),
            metadata: ResourceMetadata::fromKubernetesResponse($response['metadata']),
            spec: $response['spec'] ?? [],
            status: $response['status'] ?? [],
        );
    }
}
