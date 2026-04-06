<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner;

use InvalidArgumentException;

final readonly class HetznerClusterSpec
{
    /**
     * @param  list<string>  $controlPlaneRegions
     */
    public function __construct(
        public array $controlPlaneRegions,
        public string $hetznerSecretName,
        public ?string $sshKeyName = null,
    ) {
        if ($this->controlPlaneRegions === []) {
            throw new InvalidArgumentException('HetznerClusterSpec requires at least one controlPlaneRegion.');
        }

        if (mb_trim($this->hetznerSecretName) === '') {
            throw new InvalidArgumentException('HetznerClusterSpec requires hetznerSecretName.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $region = $this->controlPlaneRegions[0];

        $spec = [
            'controlPlaneRegions' => $this->controlPlaneRegions,
            'controlPlaneLoadBalancer' => [
                'region' => $region,
            ],
            'hetznerSecretRef' => [
                'key' => [
                    'hcloudToken' => 'hcloud',
                    'hetznerRobotUser' => 'robot-user',
                    'hetznerRobotPassword' => 'robot-password',
                ],
                'name' => $this->hetznerSecretName,
            ],
            'sshKeys' => [
                'hcloud' => [],
            ],
        ];

        if ($this->sshKeyName !== null) {
            $spec['sshKeys']['hcloud'] = [['name' => $this->sshKeyName]];
        }

        return $spec;
    }
}
