<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

final readonly class LabelSet
{
    /**
     * @param  array<string, string>  $labels
     */
    public function __construct(
        public array $labels = [],
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->labels;
    }
}
