<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner;

final readonly class HetznerClusterSpec
{
    public function __construct(
        public string $controlPlaneRegion,
        public string $sshKeyName = 'kuven',
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'controlPlaneRegion' => $this->controlPlaneRegion,
            'sshKeys' => [
                'hcloud' => [$this->sshKeyName],
            ],
        ];
    }
}
