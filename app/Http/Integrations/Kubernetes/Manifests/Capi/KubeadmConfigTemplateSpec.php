<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests\Capi;

final readonly class KubeadmConfigTemplateSpec
{
    /**
     * @param  array<string, string>  $kubeletExtraArgs
     */
    public function __construct(
        public array $kubeletExtraArgs = ['eviction-hard' => 'nodefs.available<0%,nodefs.inodesFree<0%,imagefs.available<0%'],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'template' => [
                'spec' => [
                    'joinConfiguration' => [
                        'nodeRegistration' => [
                            'kubeletExtraArgs' => $this->kubeletExtraArgs,
                        ],
                    ],
                ],
            ],
        ];
    }
}
