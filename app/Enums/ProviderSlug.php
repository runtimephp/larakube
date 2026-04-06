<?php

declare(strict_types=1);

namespace App\Enums;

enum ProviderSlug: string
{
    case Hetzner = 'hetzner';
    case DigitalOcean = 'digital_ocean';
    case Aws = 'aws';
    case Vultr = 'vultr';
    case Akamai = 'akamai';
    case Docker = 'docker';

    public function label(): string
    {
        return match ($this) {
            self::Hetzner => 'Hetzner',
            self::DigitalOcean => 'DigitalOcean',
            self::Aws => 'AWS',
            self::Vultr => 'Vultr',
            self::Akamai => 'Akamai',
            self::Docker => 'Docker',
        };
    }
}
