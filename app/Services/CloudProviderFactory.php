<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CloudProviderService;
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
            CloudProviderType::Multipass => throw new RuntimeException('Multipass server service not yet implemented.'),
        };
    }

    public function makeForValidation(CloudProviderType $type, ?string $token = null): CloudProviderService
    {
        return match ($type) {
            CloudProviderType::Hetzner => new HetznerService($token ?? ''),
            CloudProviderType::DigitalOcean => new DigitalOceanService($token ?? ''),
            CloudProviderType::Multipass => throw new RuntimeException('Multipass validation service not yet implemented.'),
        };
    }
}
