<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

use App\Http\Integrations\Kubernetes\Enums\KuvenLabel;

final readonly class LabelSet
{
    /**
     * @param  array<string, string>  $labels
     */
    public function __construct(
        public array $labels = [],
    ) {}

    public static function kuvenApp(string $name, string $component): self
    {
        return new self([
            KuvenLabel::Name->value => $name,
            KuvenLabel::Component->value => $component,
            KuvenLabel::ManagedBy->value => 'kuven',
            KuvenLabel::PartOf->value => 'kuven',
        ]);
    }

    public function with(KuvenLabel|string $key, string $value): self
    {
        $labels = $this->labels;
        $labels[$key instanceof KuvenLabel ? $key->value : $key] = $value;

        return new self($labels);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->labels;
    }
}
