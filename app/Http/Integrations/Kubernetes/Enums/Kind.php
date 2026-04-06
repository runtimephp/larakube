<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Enums;

enum Kind: string
{
    case Cluster = 'Cluster';
    case ConfigMap = 'ConfigMap';
    case Deployment = 'Deployment';
    case DockerCluster = 'DockerCluster';
    case DockerMachineTemplate = 'DockerMachineTemplate';
    case HetznerCluster = 'HetznerCluster';
    case HCloudMachineTemplate = 'HCloudMachineTemplate';
    case KubeadmConfigTemplate = 'KubeadmConfigTemplate';
    case KubeadmControlPlane = 'KubeadmControlPlane';
    case MachineDeployment = 'MachineDeployment';
    case Namespace = 'Namespace';
    case NetworkPolicy = 'NetworkPolicy';
    case ResourceQuota = 'ResourceQuota';
    case Role = 'Role';
    case RoleBinding = 'RoleBinding';
    case Secret = 'Secret';
    case ServiceAccount = 'ServiceAccount';
}
