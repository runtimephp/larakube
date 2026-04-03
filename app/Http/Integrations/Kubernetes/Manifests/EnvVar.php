<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

final readonly class EnvVar
{
    public function __construct(
        public string $name,
        public string $value,
    ) {}

    /**
     * @return array{name: string, value: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
