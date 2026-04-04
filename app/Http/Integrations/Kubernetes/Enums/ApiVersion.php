<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Enums;

enum ApiVersion: string
{
    case V1 = 'v1';
    case AppsV1 = 'apps/v1';
    case CapiBootstrapV1Beta1 = 'bootstrap.cluster.x-k8s.io/v1beta1';
    case CapiControlPlaneV1Beta1 = 'controlplane.cluster.x-k8s.io/v1beta1';
    case CapiCoreV1Beta1 = 'cluster.x-k8s.io/v1beta1';
    case CapiInfrastructureV1Beta1 = 'infrastructure.cluster.x-k8s.io/v1beta1';
    case NetworkingV1 = 'networking.k8s.io/v1';
    case RbacAuthorizationV1 = 'rbac.authorization.k8s.io/v1';
}
