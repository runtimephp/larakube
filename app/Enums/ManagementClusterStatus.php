<?php

declare(strict_types=1);

namespace App\Enums;

enum ManagementClusterStatus: string
{
    case Bootstrapping = 'bootstrapping';
    case Ready = 'ready';
    case Failed = 'failed';
}
