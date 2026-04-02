<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Data;

use Carbon\CarbonImmutable;

final readonly class ConditionData
{
    public function __construct(
        public string $type,
        public string $status,
        public ?string $reason = null,
        public ?string $message = null,
        public ?CarbonImmutable $lastTransitionTime = null,
    ) {}

    /**
     * @param  array<string, mixed>  $condition
     */
    public static function fromKubernetesResponse(array $condition): self
    {
        return new self(
            type: $condition['type'],
            status: $condition['status'],
            reason: $condition['reason'] ?? null,
            message: $condition['message'] ?? null,
            lastTransitionTime: isset($condition['lastTransitionTime'])
                ? CarbonImmutable::parse($condition['lastTransitionTime'])
                : null,
        );
    }

    public function isTrue(): bool
    {
        return $this->status === 'True';
    }
}
