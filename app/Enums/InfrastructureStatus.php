<?php

declare(strict_types=1);

namespace App\Enums;

enum InfrastructureStatus: string
{
    case Provisioning = 'provisioning';
    case Healthy = 'healthy';
    case Degraded = 'degraded';
    case Failed = 'failed';
    case Destroyed = 'destroyed';

    public function label(): string
    {
        return match ($this) {
            self::Provisioning => 'Provisioning',
            self::Healthy => 'Healthy',
            self::Degraded => 'Degraded',
            self::Failed => 'Failed',
            self::Destroyed => 'Destroyed',
        };
    }
}
