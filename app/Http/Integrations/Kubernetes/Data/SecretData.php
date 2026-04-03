<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

use SensitiveParameter;

final readonly class SecretData
{
    /**
     * @param  array<string, string>  $data
     */
    public function __construct(
        public ResourceMetadata $metadata,
        public string $type,
        #[SensitiveParameter] public array $data = [],
    ) {}

    /**
     * @param  array{metadata: array{name: string, uid: string, resourceVersion: string, creationTimestamp: string, namespace?: string, labels?: array<string, string>, annotations?: array<string, string>}, type?: string, data?: array<string, string>}  $response
     */
    public static function fromKubernetesResponse(array $response): self
    {
        $decodedData = [];

        foreach ($response['data'] ?? [] as $key => $value) {
            $decoded = base64_decode((string) $value, true);
            $decodedData[$key] = $decoded === false ? (string) $value : $decoded;
        }

        return new self(
            metadata: ResourceMetadata::fromKubernetesResponse($response['metadata']),
            type: $response['type'] ?? 'Opaque',
            data: $decodedData,
        );
    }
}
