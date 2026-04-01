<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * @see ADR-0005 — Superseded by CAPI; scheduled for removal
 */
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
