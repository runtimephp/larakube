<?php

declare(strict_types=1);

use App\Actions\CreateCloudProvider;
use App\Contracts\CloudProviderClient;
use App\Contracts\CloudProviderClientFactoryInterface;
use App\Data\CreateCloudProviderData;
use App\Enums\CloudProviderType;
use App\Models\Organization;

test('create cloud provider with valid token', function (): void {
    $mockClient = Mockery::mock(CloudProviderClient::class);
    $mockClient->shouldReceive('validateToken')->once()->andReturnTrue();

    $mockFactory = Mockery::mock(CloudProviderClientFactoryInterface::class);
    $mockFactory->shouldReceive('make')->with(CloudProviderType::Hetzner)->once()->andReturn($mockClient);

    $organization = Organization::factory()->create();

    $action = new CreateCloudProvider($mockFactory);
    $cloudProvider = $action->handle(
        new CreateCloudProviderData(
            name: 'Hetzner Production',
            type: CloudProviderType::Hetzner,
            apiToken: 'valid-token',
        ),
        $organization,
    );

    expect($cloudProvider->name)->toBe('Hetzner Production')
        ->and($cloudProvider->type)->toBe(CloudProviderType::Hetzner)
        ->and($cloudProvider->is_verified)->toBeTrue()
        ->and($cloudProvider->organization_id)->toBe($organization->id);
});

test('create cloud provider with invalid token throws exception', function (): void {
    $mockClient = Mockery::mock(CloudProviderClient::class);
    $mockClient->shouldReceive('validateToken')->once()->andReturnFalse();

    $mockFactory = Mockery::mock(CloudProviderClientFactoryInterface::class);
    $mockFactory->shouldReceive('make')->with(CloudProviderType::DigitalOcean)->once()->andReturn($mockClient);

    $organization = Organization::factory()->create();

    $action = new CreateCloudProvider($mockFactory);
    $action->handle(
        new CreateCloudProviderData(
            name: 'DO Staging',
            type: CloudProviderType::DigitalOcean,
            apiToken: 'invalid-token',
        ),
        $organization,
    );
})->throws(RuntimeException::class, 'The API token for DigitalOcean is invalid.');
