<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner;

use InvalidArgumentException;

final readonly class HetznerClusterSpec
{
    public function __construct(
        public string $controlPlaneRegion,
        public string $sshKeyName,
    ) {
        if (mb_trim($this->controlPlaneRegion) === '' || mb_trim($this->sshKeyName) === '') {
            throw new InvalidArgumentException('HetznerClusterSpec requires controlPlaneRegion and sshKeyName.');
        }
    }

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
