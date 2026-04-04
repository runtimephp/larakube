<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Enums;

enum KuvenResource: string
{
    case Operator = 'kuven-operator';
    case DefaultDenyPolicy = 'default-deny';
    case TenantQuota = 'tenant-quota';
}
