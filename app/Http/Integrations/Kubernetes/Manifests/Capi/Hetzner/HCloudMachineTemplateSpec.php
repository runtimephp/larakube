<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner;

final readonly class HCloudMachineTemplateSpec
{
    public function __construct(
        public string $type,
        public string $imageName = 'ubuntu-24.04',
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'template' => [
                'spec' => [
                    'type' => $this->type,
                    'imageName' => $this->imageName,
                ],
            ],
        ];
    }
}
