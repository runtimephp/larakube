<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Enums;

enum KuvenLabel: string
{
    case Component = 'app.kubernetes.io/component';
    case ManagedBy = 'app.kubernetes.io/managed-by';
    case Name = 'app.kubernetes.io/name';
    case Organization = 'kuven.io/organization';
    case PartOf = 'app.kubernetes.io/part-of';
}
