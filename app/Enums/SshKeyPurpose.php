<?php

declare(strict_types=1);

namespace App\Enums;

enum SshKeyPurpose: string
{
    case Bastion = 'bastion';
    case Node = 'node';

    public function label(): string
    {
        return match ($this) {
            self::Bastion => 'Bastion',
            self::Node => 'Node',
        };
    }
}
