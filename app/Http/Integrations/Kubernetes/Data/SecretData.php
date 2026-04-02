<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

final readonly class SecretData
{
    /**
     * @param  array<string, string>  $data
     */
    public function __construct(
        public ResourceMetadata $metadata,
        public string $type,
        public array $data = [],
    ) {}

    /**
     * @param  array<string, mixed>  $response
     */
    public static function fromKubernetesResponse(array $response): self
    {
        $decodedData = [];

        foreach ($response['data'] ?? [] as $key => $value) {
            $decodedData[$key] = base64_decode((string) $value, true) ?: $value;
        }

        return new self(
            metadata: ResourceMetadata::fromKubernetesResponse($response['metadata']),
            type: $response['type'] ?? 'Opaque',
            data: $decodedData,
        );
    }
}
