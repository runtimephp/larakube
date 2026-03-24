<?php

declare(strict_types=1);

use App\Enums\CloudProviderType;
use App\Services\DigitalOcean\DigitalOceanServerService;
use App\Services\DigitalOcean\DigitalOceanService;
use App\Services\Factories\CloudProviderServiceFactory;
use App\Services\Hetzner\HetznerServerService;
use App\Services\Hetzner\HetznerService;

test('make server service returns digital ocean server service', function (): void {
    $factory = new CloudProviderServiceFactory;
    $service = $factory->makeServerService(CloudProviderType::DigitalOcean);

    expect($service)->toBeInstanceOf(DigitalOceanServerService::class);
});

test('make server service returns hetzner server service', function (): void {
    $factory = new CloudProviderServiceFactory;
    $service = $factory->makeServerService(CloudProviderType::Hetzner);

    expect($service)->toBeInstanceOf(HetznerServerService::class);
});

test('make base service returns digital ocean service', function (): void {
    $factory = new CloudProviderServiceFactory;
    $service = $factory->makeBaseService(CloudProviderType::DigitalOcean);

    expect($service)->toBeInstanceOf(DigitalOceanService::class);
});

test('make base service returns hetzner service', function (): void {
    $factory = new CloudProviderServiceFactory;
    $service = $factory->makeBaseService(CloudProviderType::Hetzner);

    expect($service)->toBeInstanceOf(HetznerService::class);
});
