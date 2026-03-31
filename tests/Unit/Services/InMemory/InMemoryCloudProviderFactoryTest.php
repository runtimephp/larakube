<?php

declare(strict_types=1);

use App\Enums\CloudProviderType;
use App\Services\InMemory\InMemoryCloudProviderFactory;
use App\Services\InMemory\InMemoryNatGatewayService;

test('makeNatGatewayService returns injected service', function (): void {
    $natService = new InMemoryNatGatewayService();
    $factory = new InMemoryCloudProviderFactory(natGatewayService: $natService);

    $result = $factory->makeNatGatewayService(CloudProviderType::Hetzner, 'test-token');

    expect($result)->toBe($natService);
});

test('makeNatGatewayService falls back to parent when not provided', function (): void {
    $factory = new InMemoryCloudProviderFactory();

    $result = $factory->makeNatGatewayService(CloudProviderType::Multipass);

    expect($result)->toBeInstanceOf(App\Services\MultipassNatGatewayService::class);
});
