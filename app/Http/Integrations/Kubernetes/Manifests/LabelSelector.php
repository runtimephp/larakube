<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

final readonly class LabelSelector
{
    public function __construct(
        public LabelSet $matchLabels,
    ) {}

    /**
     * @return array{matchLabels: array<string, string>}
     */
    public function toArray(): array
    {
        return [
            'matchLabels' => $this->matchLabels->toArray(),
        ];
    }
}
