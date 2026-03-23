<?php

declare(strict_types=1);

namespace App\Enums;

enum CloudProviderType: string
{
    case Hetzner = 'hetzner';
    case DigitalOcean = 'digital_ocean';

    public function label(): string
    {
        return match ($this) {
            self::Hetzner => 'Hetzner',
            self::DigitalOcean => 'DigitalOcean',
        };
    }
}
