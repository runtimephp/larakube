<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Enums;

enum ApiVersion: string
{
    case V1 = 'v1';
    case AppsV1 = 'apps/v1';
    case RbacAuthorizationV1 = 'rbac.authorization.k8s.io/v1';
}
