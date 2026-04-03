<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

use App\Http\Integrations\Kubernetes\Enums\StatusReason;

final readonly class StatusData
{
    public function __construct(
        public string $message,
        public StatusReason $reason,
        public int $code,
        public ?string $group = null,
        public ?string $resource = null,
    ) {}

    /**
     * @param  array<string, mixed>  $response
     */
    public static function fromKubernetesResponse(array $response): self
    {
        return new self(
            message: $response['message'] ?? 'Unknown error',
            reason: StatusReason::tryFrom($response['reason'] ?? '') ?? StatusReason::Unknown,
            code: $response['code'] ?? 0,
            group: $response['details']['group'] ?? null,
            resource: $response['details']['kind'] ?? null,
        );
    }
}
