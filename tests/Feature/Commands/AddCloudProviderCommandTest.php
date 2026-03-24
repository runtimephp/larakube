<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Contracts\ServiceFactoryInterface;
use App\Data\SessionOrganizationData;
use App\Enums\CloudProviderType;
use App\Models\Organization;
use App\Models\User;
use App\Services\Hetzner\HetznerService;

beforeEach(function (): void {
    $tempPath = sys_get_temp_dir().'/add-cloud-provider-test-'.uniqid().'/session.json';
    $this->app->singleton(SessionManager::class, fn () => new SessionManager($tempPath));
});

test('add cloud provider command creates provider with valid token', function (): void {
    $mockService = Mockery::mock(HetznerService::class);
    $mockService->shouldReceive('validateToken')->once()->andReturnTrue();

    $mockFactory = Mockery::mock(ServiceFactoryInterface::class);
    $mockFactory->shouldReceive('makeBaseService')->with(CloudProviderType::Hetzner)->once()->andReturn($mockService);
    $this->app->instance(ServiceFactoryInterface::class, $mockFactory);

    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    $userData = new LoginUser()->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    $this->artisan('cloud-provider:add')
        ->expectsQuestion('Select a cloud provider', 'hetzner')
        ->expectsQuestion('Name for this provider', 'Hetzner Production')
        ->expectsQuestion('API token', 'valid-token')
        ->expectsOutputToContain('Cloud provider [Hetzner Production] added successfully')
        ->assertSuccessful();

    $this->assertDatabaseHas('cloud_providers', [
        'organization_id' => $organization->id,
        'name' => 'Hetzner Production',
        'type' => 'hetzner',
        'is_verified' => true,
    ]);
});

test('add cloud provider command fails with invalid token', function (): void {
    $mockService = Mockery::mock(HetznerService::class);
    $mockService->shouldReceive('validateToken')->once()->andReturnFalse();

    $mockFactory = Mockery::mock(ServiceFactoryInterface::class);
    $mockFactory->shouldReceive('makeBaseService')->with(CloudProviderType::Hetzner)->once()->andReturn($mockService);
    $this->app->instance(ServiceFactoryInterface::class, $mockFactory);

    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    $userData = new LoginUser()->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    $this->artisan('cloud-provider:add')
        ->expectsQuestion('Select a cloud provider', 'hetzner')
        ->expectsQuestion('Name for this provider', 'Hetzner Staging')
        ->expectsQuestion('API token', 'invalid-token')
        ->expectsOutputToContain('The API token for Hetzner is invalid')
        ->assertFailed();
});

test('add cloud provider command fails when not authenticated', function (): void {
    $this->artisan('cloud-provider:add')
        ->expectsOutputToContain('You are not logged in')
        ->assertFailed();
});
