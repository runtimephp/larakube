<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CloudProviderService;
use App\Contracts\ServerService;
use App\Enums\CloudProviderType;

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

    public function makeForValidation(CloudProviderType $type, ?string $token = null): CloudProviderService
    {
        return match ($type) {
            CloudProviderType::Hetzner => new HetznerService($token ?? ''),
            CloudProviderType::DigitalOcean => new DigitalOceanService($token ?? ''),
            CloudProviderType::Multipass => new MultipassService(),
        };
    }
}
