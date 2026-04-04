<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Enums;

enum Kind: string
{
    case ConfigMap = 'ConfigMap';
    case Deployment = 'Deployment';
    case Namespace = 'Namespace';
    case NetworkPolicy = 'NetworkPolicy';
    case ResourceQuota = 'ResourceQuota';
    case Role = 'Role';
    case RoleBinding = 'RoleBinding';
    case Secret = 'Secret';
    case ServiceAccount = 'ServiceAccount';
}
