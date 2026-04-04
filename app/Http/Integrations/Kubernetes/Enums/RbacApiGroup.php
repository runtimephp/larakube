<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Enums;

enum RbacApiGroup: string
{
    case Core = '';
    case CapiCore = 'cluster.x-k8s.io';
    case CapiInfrastructure = 'infrastructure.cluster.x-k8s.io';
    case CapiBootstrap = 'bootstrap.cluster.x-k8s.io';
    case CapiControlPlane = 'controlplane.cluster.x-k8s.io';
}
