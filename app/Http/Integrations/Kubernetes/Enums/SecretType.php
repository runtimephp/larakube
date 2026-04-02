<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Enums;

enum SecretType: string
{
    case DockerConfigJson = 'kubernetes.io/dockerconfigjson';
    case Opaque = 'Opaque';
    case Tls = 'kubernetes.io/tls';
}
