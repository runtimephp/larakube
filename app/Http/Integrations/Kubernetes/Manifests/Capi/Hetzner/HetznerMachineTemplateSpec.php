<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner;

final readonly class HetznerMachineTemplateSpec
{
    public function __construct(
        public string $serverType,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'template' => [
                'spec' => [
                    'serverType' => $this->serverType,
                ],
            ],
        ];
    }
}
