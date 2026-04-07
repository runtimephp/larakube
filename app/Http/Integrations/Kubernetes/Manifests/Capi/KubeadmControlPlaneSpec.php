<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests\Capi;

use App\Http\Integrations\Kubernetes\Manifests\ObjectReference;

final readonly class KubeadmControlPlaneSpec
{
    /**
     * @param  array<string, string>  $controllerManagerExtraArgs
     * @param  array<string, string>  $kubeletExtraArgs
     */
    public function __construct(
        public int $replicas,
        public string $version,
        public ObjectReference $infrastructureRef,
        public array $controllerManagerExtraArgs = ['cloud-provider' => 'external'],
        public array $kubeletExtraArgs = [
            'cloud-provider' => 'external',
            'eviction-hard' => 'nodefs.available<0%,nodefs.inodesFree<0%,imagefs.available<0%',
        ],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'replicas' => $this->replicas,
            'version' => $this->version,
            'machineTemplate' => [
                'infrastructureRef' => $this->infrastructureRef->toArray(),
            ],
            'kubeadmConfigSpec' => [
                'clusterConfiguration' => [
                    'controllerManager' => [
                        'extraArgs' => $this->controllerManagerExtraArgs,
                    ],
                ],
                'initConfiguration' => [
                    'nodeRegistration' => [
                        'kubeletExtraArgs' => $this->kubeletExtraArgs,
                    ],
                ],
                'joinConfiguration' => [
                    'nodeRegistration' => [
                        'kubeletExtraArgs' => $this->kubeletExtraArgs,
                    ],
                ],
            ],
        ];
    }
}
