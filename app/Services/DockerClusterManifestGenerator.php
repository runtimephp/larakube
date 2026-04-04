<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClusterManifestGenerator;
use App\Data\CreateClusterManifestData;

final class DockerClusterManifestGenerator implements ClusterManifestGenerator
{
    public function generate(CreateClusterManifestData $createClusterManifestData): array
    {
        $name = $createClusterManifestData->name;
        $namespace = $createClusterManifestData->namespace;
        $version = $createClusterManifestData->kubernetesVersion;
        $cpCount = $createClusterManifestData->controlPlaneCount;
        $workerCount = $createClusterManifestData->workerCount;

        return [
            $this->cluster($name, $namespace),
            $this->dockerCluster($name, $namespace),
            $this->kubeadmControlPlane($name, $namespace, $version, $cpCount),
            $this->controlPlaneMachineTemplate($name, $namespace),
            $this->machineDeployment($name, $namespace, $version, $workerCount),
            $this->workerMachineTemplate($name, $namespace),
            $this->kubeadmConfigTemplate($name, $namespace),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function cluster(string $name, string $namespace): array
    {
        return [
            'apiVersion' => 'cluster.x-k8s.io/v1beta1',
            'kind' => 'Cluster',
            'metadata' => [
                'name' => $name,
                'namespace' => $namespace,
            ],
            'spec' => [
                'clusterNetwork' => [
                    'pods' => ['cidrBlocks' => ['192.168.0.0/16']],
                    'services' => ['cidrBlocks' => ['10.128.0.0/12']],
                ],
                'controlPlaneRef' => [
                    'apiVersion' => 'controlplane.cluster.x-k8s.io/v1beta1',
                    'kind' => 'KubeadmControlPlane',
                    'name' => "{$name}-control-plane",
                ],
                'infrastructureRef' => [
                    'apiVersion' => 'infrastructure.cluster.x-k8s.io/v1beta1',
                    'kind' => 'DockerCluster',
                    'name' => $name,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dockerCluster(string $name, string $namespace): array
    {
        return [
            'apiVersion' => 'infrastructure.cluster.x-k8s.io/v1beta1',
            'kind' => 'DockerCluster',
            'metadata' => [
                'name' => $name,
                'namespace' => $namespace,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function kubeadmControlPlane(string $name, string $namespace, string $version, int $replicas): array
    {
        return [
            'apiVersion' => 'controlplane.cluster.x-k8s.io/v1beta1',
            'kind' => 'KubeadmControlPlane',
            'metadata' => [
                'name' => "{$name}-control-plane",
                'namespace' => $namespace,
            ],
            'spec' => [
                'replicas' => $replicas,
                'version' => $version,
                'machineTemplate' => [
                    'infrastructureRef' => [
                        'apiVersion' => 'infrastructure.cluster.x-k8s.io/v1beta1',
                        'kind' => 'DockerMachineTemplate',
                        'name' => "{$name}-control-plane",
                    ],
                ],
                'kubeadmConfigSpec' => [
                    'clusterConfiguration' => [
                        'controllerManager' => [
                            'extraArgs' => [
                                'enable-hostpath-provisioner' => 'true',
                            ],
                        ],
                    ],
                    'initConfiguration' => [
                        'nodeRegistration' => [
                            'kubeletExtraArgs' => [
                                'eviction-hard' => 'nodefs.available<0%,nodefs.inodesFree<0%,imagefs.available<0%',
                            ],
                        ],
                    ],
                    'joinConfiguration' => [
                        'nodeRegistration' => [
                            'kubeletExtraArgs' => [
                                'eviction-hard' => 'nodefs.available<0%,nodefs.inodesFree<0%,imagefs.available<0%',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function controlPlaneMachineTemplate(string $name, string $namespace): array
    {
        return [
            'apiVersion' => 'infrastructure.cluster.x-k8s.io/v1beta1',
            'kind' => 'DockerMachineTemplate',
            'metadata' => [
                'name' => "{$name}-control-plane",
                'namespace' => $namespace,
            ],
            'spec' => [
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
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function machineDeployment(string $name, string $namespace, string $version, int $replicas): array
    {
        return [
            'apiVersion' => 'cluster.x-k8s.io/v1beta1',
            'kind' => 'MachineDeployment',
            'metadata' => [
                'name' => "{$name}-md-0",
                'namespace' => $namespace,
            ],
            'spec' => [
                'clusterName' => $name,
                'replicas' => $replicas,
                'selector' => [
                    'matchLabels' => (object) [],
                ],
                'template' => [
                    'spec' => [
                        'version' => $version,
                        'clusterName' => $name,
                        'bootstrap' => [
                            'configRef' => [
                                'apiVersion' => 'bootstrap.cluster.x-k8s.io/v1beta1',
                                'kind' => 'KubeadmConfigTemplate',
                                'name' => "{$name}-md-0",
                            ],
                        ],
                        'infrastructureRef' => [
                            'apiVersion' => 'infrastructure.cluster.x-k8s.io/v1beta1',
                            'kind' => 'DockerMachineTemplate',
                            'name' => "{$name}-md-0",
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function workerMachineTemplate(string $name, string $namespace): array
    {
        return [
            'apiVersion' => 'infrastructure.cluster.x-k8s.io/v1beta1',
            'kind' => 'DockerMachineTemplate',
            'metadata' => [
                'name' => "{$name}-md-0",
                'namespace' => $namespace,
            ],
            'spec' => [
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
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function kubeadmConfigTemplate(string $name, string $namespace): array
    {
        return [
            'apiVersion' => 'bootstrap.cluster.x-k8s.io/v1beta1',
            'kind' => 'KubeadmConfigTemplate',
            'metadata' => [
                'name' => "{$name}-md-0",
                'namespace' => $namespace,
            ],
            'spec' => [
                'template' => [
                    'spec' => [
                        'joinConfiguration' => [
                            'nodeRegistration' => [
                                'kubeletExtraArgs' => [
                                    'eviction-hard' => 'nodefs.available<0%,nodefs.inodesFree<0%,imagefs.available<0%',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
