<?php

declare(strict_types=1);

use App\Enums\CloudProviderType;
use App\Services\CloudProviders\CloudProviderClientFactory;
use App\Services\CloudProviders\DigitalOceanClient;
use App\Services\CloudProviders\HetznerClient;

test('make returns hetzner client', function (): void {
    $factory = new CloudProviderClientFactory;

    expect($factory->make(CloudProviderType::Hetzner))
        ->toBeInstanceOf(HetznerClient::class);
});

test('make returns digital ocean client', function (): void {
    $factory = new CloudProviderClientFactory;

    expect($factory->make(CloudProviderType::DigitalOcean))
        ->toBeInstanceOf(DigitalOceanClient::class);
});
