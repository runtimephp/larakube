<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;

final readonly class KubernetesVersionData
{
    public function __construct(
        public string $name,
        public CarbonImmutable $releaseDate,
        public CarbonImmutable $endOfLife,
    ) {}

    public function isSupported(): bool
    {
        return $this->endOfLife->isFuture();
    }

    public function isEndOfLife(): bool
    {
        return $this->endOfLife->isPast();
    }
}
