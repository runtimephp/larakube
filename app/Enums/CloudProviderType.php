<?php

declare(strict_types=1);

namespace App\Enums;

use App\Data\ServerSpecData;

enum CloudProviderType: string
{
    case Hetzner = 'hetzner';
    case DigitalOcean = 'digital_ocean';
    case Multipass = 'multipass';

    public function label(): string
    {
        return match ($this) {
            self::Hetzner => 'Hetzner',
            self::DigitalOcean => 'DigitalOcean',
            self::Multipass => 'Multipass (Local)',
        };
    }

    /**
     * @return list<string>
     */
    public function dnsServers(): array
    {
        return match ($this) {
            self::Hetzner => ['185.12.64.1', '185.12.64.2'],
            self::DigitalOcean => ['67.207.67.2', '67.207.67.3'],
            self::Multipass => ['1.1.1.1', '8.8.8.8'],
        };
    }

    public function sshUser(): string
    {
        return match ($this) {
            self::Hetzner => 'root',
            self::DigitalOcean => 'root',
            self::Multipass => 'ubuntu',
        };
    }

    public function bastionSpec(): ServerSpecData
    {
        return match ($this) {
            self::Hetzner => new ServerSpecData(type: 'cpx22', image: 'ubuntu-24.04', region: 'hel1'),
            self::DigitalOcean => new ServerSpecData(type: 's-1vcpu-2gb', image: 'ubuntu-24-04-x64', region: 'ams3'),
            self::Multipass => new ServerSpecData(type: 'custom', image: 'noble', region: 'local', cpus: 1, memory: '1G', disk: '10G'),
        };
    }

    public function controlPlaneSpec(): ServerSpecData
    {
        return match ($this) {
            self::Hetzner => new ServerSpecData(type: 'cpx32', image: 'ubuntu-24.04', region: 'hel1'),
            self::DigitalOcean => new ServerSpecData(type: 's-4vcpu-8gb', image: 'ubuntu-24-04-x64', region: 'ams3'),
            self::Multipass => new ServerSpecData(type: 'custom', image: 'noble', region: 'local', cpus: 2, memory: '4G', disk: '20G'),
        };
    }

    public function workerSpec(): ServerSpecData
    {
        return match ($this) {
            self::Hetzner => new ServerSpecData(type: 'cpx32', image: 'ubuntu-24.04', region: 'hel1'),
            self::DigitalOcean => new ServerSpecData(type: 's-4vcpu-8gb', image: 'ubuntu-24-04-x64', region: 'ams3'),
            self::Multipass => new ServerSpecData(type: 'custom', image: 'noble', region: 'local', cpus: 2, memory: '2G', disk: '20G'),
        };
    }
}
