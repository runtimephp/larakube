<?php

declare(strict_types=1);

namespace App\Enums;

enum ServerStatus: string
{
    case Running = 'running';
    case Off = 'off';
    case Starting = 'starting';
    case Unknown = 'unknown';

    public static function fromHetzner(string $status): self
    {
        return match ($status) {
            'running' => self::Running,
            'off' => self::Off,
            'initializing', 'starting' => self::Starting,
            default => self::Unknown,
        };
    }

    public static function fromDigitalOcean(string $status): self
    {
        return match ($status) {
            'active' => self::Running,
            'off' => self::Off,
            'new' => self::Starting,
            default => self::Unknown,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Running => 'Running',
            self::Off => 'Off',
            self::Starting => 'Starting',
            self::Unknown => 'Unknown',
        };
    }
}
