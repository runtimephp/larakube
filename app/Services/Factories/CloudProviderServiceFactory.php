<?php

declare(strict_types=1);

namespace App\Services\Factories;

use App\Contracts\ServerServiceContract;
use App\Contracts\ServiceFactoryInterface;
use App\Enums\CloudProviderType;
use App\Services\DigitalOcean\DigitalOceanServerService;
use App\Services\DigitalOcean\DigitalOceanService;
use App\Services\Hetzner\HetznerServerService;
use App\Services\Hetzner\HetznerService;

final class CloudProviderServiceFactory implements ServiceFactoryInterface
{
    public function makeServerService(CloudProviderType $type): ServerServiceContract
    {
        return match ($type) {
            CloudProviderType::Hetzner => new HetznerServerService,
            CloudProviderType::DigitalOcean => new DigitalOceanServerService,
        };
    }

    public function makeBaseService(CloudProviderType $type): object
    {
        return match ($type) {
            CloudProviderType::Hetzner => new HetznerService,
            CloudProviderType::DigitalOcean => new DigitalOceanService,
        };
    }
}
