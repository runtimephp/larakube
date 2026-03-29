<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CloudProviderService;
use App\Contracts\FirewallService;
use App\Contracts\NetworkService;
use App\Contracts\ServerService;
use App\Enums\CloudProviderType;
use RuntimeException;

class CloudProviderFactory
{
    public function makeServerService(CloudProviderType $type, ?string $token = null): ServerService
    {
        return match ($type) {
            CloudProviderType::Hetzner => new HetznerServerService($token ?? ''),
            CloudProviderType::DigitalOcean => new DigitalOceanServerService($token ?? ''),
            CloudProviderType::Multipass => new MultipassServerService(),
        };
    }

    public function makeNetworkService(CloudProviderType $type, ?string $token = null): NetworkService
    {
        return match ($type) {
            CloudProviderType::Hetzner => new HetznerNetworkService($token ?? ''),
            CloudProviderType::DigitalOcean => throw new RuntimeException('Network service for DigitalOcean is not yet implemented.'),
            CloudProviderType::Multipass => new MultipassNetworkService(),
        };
    }

    public function makeFirewallService(CloudProviderType $type, ?string $token = null): FirewallService
    {
        return match ($type) {
            CloudProviderType::Hetzner => new HetznerFirewallService($token ?? ''),
            CloudProviderType::DigitalOcean => throw new RuntimeException('Firewall service for DigitalOcean is not yet implemented.'),
            CloudProviderType::Multipass => new MultipassFirewallService(),
        };
    }

    public function makeForValidation(CloudProviderType $type, ?string $token = null): CloudProviderService
    {
        return match ($type) {
            CloudProviderType::Hetzner => new HetznerService($token ?? ''),
            CloudProviderType::DigitalOcean => new DigitalOceanService($token ?? ''),
            CloudProviderType::Multipass => new MultipassService(),
        };
    }
}
