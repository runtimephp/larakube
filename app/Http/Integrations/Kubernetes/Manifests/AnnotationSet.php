<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

final readonly class AnnotationSet
{
    /**
     * @param  array<string, string>  $annotations
     */
    public function __construct(
        public array $annotations = [],
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->annotations;
    }
}
