<?php

declare(strict_types=1);

namespace App\Enums;

enum ClusterTopology: string
{
    case SingleCp = 'single_cp';
    case Ha = 'ha';

    public function label(): string
    {
        return match ($this) {
            self::SingleCp => 'Single Control Plane',
            self::Ha => 'High Availability',
        };
    }
}
