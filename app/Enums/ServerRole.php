<?php

declare(strict_types=1);

namespace App\Enums;

enum ServerRole: string
{
    case Bastion = 'bastion';
    case ControlPlane = 'control_plane';
    case Node = 'node';

    public function label(): string
    {
        return match ($this) {
            self::Bastion => 'Bastion',
            self::ControlPlane => 'Control Plane',
            self::Node => 'Node',
        };
    }
}
