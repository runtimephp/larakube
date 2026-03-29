<?php

declare(strict_types=1);

namespace App\Enums;

enum ProvisioningPhase: string
{
    case Infrastructure = 'infrastructure';
    case Configuration = 'configuration';

    public function label(): string
    {
        return match ($this) {
            self::Infrastructure => 'Infrastructure',
            self::Configuration => 'Configuration',
        };
    }
}
