<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests\Capi\Docker;

final readonly class DockerMachineTemplateSpec
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'template' => [
                'spec' => [
                    'extraMounts' => [
                        [
                            'containerPath' => '/var/run/docker.sock',
                            'hostPath' => '/var/run/docker.sock',
                        ],
                    ],
                ],
            ],
        ];
    }
}
