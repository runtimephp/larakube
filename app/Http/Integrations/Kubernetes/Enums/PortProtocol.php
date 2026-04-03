<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Enums;

enum PortProtocol: string
{
    case Tcp = 'TCP';
    case Udp = 'UDP';
    case Sctp = 'SCTP';
}
