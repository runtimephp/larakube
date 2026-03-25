<?php

declare(strict_types=1);

use App\Actions\CreateCloudProvider;
use App\Data\CreateCloudProviderData;
use App\Enums\CloudProviderType;
use App\Models\Organization;
use App\Services\CloudProviderFactory;

test('create cloud provider with valid token', function (): void {
    $mockService = Mockery::mock(CloudProviderService::class);
    $mockService->shouldReceive('validateToken')->once()->andReturnTrue();

    $mockFactory = Mockery::mock(CloudProviderFactory::class);
    $mockFactory->shouldReceive('makeForValidation')->with(CloudProviderType::Hetzner, 'valid-token')->once()->andReturn($mockService);

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
    $mockService = Mockery::mock(CloudProviderService::class);
    $mockService->shouldReceive('validateToken')->once()->andReturnFalse();

    $mockFactory = Mockery::mock(CloudProviderFactory::class);
    $mockFactory->shouldReceive('makeForValidation')->with(CloudProviderType::DigitalOcean, 'invalid-token')->once()->andReturn($mockService);

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
