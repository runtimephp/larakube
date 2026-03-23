<?php

declare(strict_types=1);

namespace App\Services\CloudProviders;

use App\Contracts\CloudProviderClient;
use App\Enums\CloudProviderType;

class CloudProviderClientFactory
{
    public function make(CloudProviderType $type): CloudProviderClient
    {
        return match ($type) {
            CloudProviderType::Hetzner => new HetznerClient,
            CloudProviderType::DigitalOcean => new DigitalOceanClient,
        };
    }
}
