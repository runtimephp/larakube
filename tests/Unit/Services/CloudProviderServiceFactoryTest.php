<?php

declare(strict_types=1);

use App\Enums\CloudProviderType;
use App\Services\CloudProviderFactory;
use App\Services\DigitalOceanServerService;
use App\Services\DigitalOceanService;
use App\Services\HetznerNetworkService;
use App\Services\HetznerServerService;
use App\Services\HetznerService;
use App\Services\MultipassNetworkService;

test('make network service returns hetzner network service', function (): void {
    $factory = new CloudProviderFactory;
    $service = $factory->makeNetworkService(CloudProviderType::Hetzner, 'token');

    expect($service)->toBeInstanceOf(HetznerNetworkService::class);
});

test('make network service returns multipass network service', function (): void {
    $factory = new CloudProviderFactory;
    $service = $factory->makeNetworkService(CloudProviderType::Multipass);

    expect($service)->toBeInstanceOf(MultipassNetworkService::class);
});

test('make network service throws for digital ocean', function (): void {
    $factory = new CloudProviderFactory;

    $factory->makeNetworkService(CloudProviderType::DigitalOcean, 'token');
})->throws(RuntimeException::class, 'Network service for DigitalOcean is not yet implemented.');

test('make server service returns digital ocean server service', function (): void {
    $factory = new CloudProviderFactory;
    $service = $factory->makeServerService(CloudProviderType::DigitalOcean, 'token');

    expect($service)->toBeInstanceOf(DigitalOceanServerService::class);
});

test('make server service returns hetzner server service', function (): void {
    $factory = new CloudProviderFactory;
    $service = $factory->makeServerService(CloudProviderType::Hetzner, 'token');

    expect($service)->toBeInstanceOf(HetznerServerService::class);
});

test('make for validation returns digital ocean service', function (): void {
    $factory = new CloudProviderFactory;
    $service = $factory->makeForValidation(CloudProviderType::DigitalOcean, 'token');

    expect($service)->toBeInstanceOf(DigitalOceanService::class);
});

test('make for validation returns hetzner service', function (): void {
    $factory = new CloudProviderFactory;
    $service = $factory->makeForValidation(CloudProviderType::Hetzner, 'token');

    expect($service)->toBeInstanceOf(HetznerService::class);
});
