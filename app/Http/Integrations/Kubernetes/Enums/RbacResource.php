<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Enums;

enum RbacResource: string
{
    case All = '*';
    case ConfigMaps = 'configmaps';
    case Secrets = 'secrets';
}
