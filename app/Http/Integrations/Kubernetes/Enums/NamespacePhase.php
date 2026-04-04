<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Enums;

enum NamespacePhase: string
{
    case Active = 'Active';
    case Terminating = 'Terminating';
}
