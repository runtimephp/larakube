<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Contracts\ServerService;
use App\Data\ServerData;
use App\Data\SessionOrganizationData;
use App\Enums\ServerStatus;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\User;
use App\Services\CloudProviderFactory;

beforeEach(function (): void {
    $tempPath = sys_get_temp_dir().'/create-server-test-'.uniqid().'/session.json';
    $this->app->singleton(SessionManager::class, fn () => new SessionManager($tempPath));
});

test('create server command creates server successfully', function (): void {
    $mockServerService = Mockery::mock(ServerService::class);
    $mockServerService->shouldReceive('create')
        ->once()
        ->andReturn(new ServerData(
            externalId: 456,
            name: 'web-1',
            status: ServerStatus::Starting,
            type: 'cx11',
            region: 'fsn1',
            ipv4: null,
        ));

    $mockFactory = Mockery::mock(CloudProviderFactory::class);
    $mockFactory->shouldReceive('makeServerService')
        ->once()
        ->andReturn($mockServerService);
    $this->app->instance(CloudProviderFactory::class, $mockFactory);

    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    $provider = CloudProvider::factory()->hetzner()->create([
        'organization_id' => $organization->id,
        'name' => 'Hetzner Prod',
    ]);

    $infrastructure = Infrastructure::factory()->create([
        'organization_id' => $organization->id,
        'cloud_provider_id' => $provider->id,
    ]);

    $userData = new LoginUser()->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    $this->artisan('server:create')
        ->expectsQuestion('Select a cloud provider', $provider->id)
        ->expectsQuestion('Select an infrastructure', $infrastructure->id)
        ->expectsQuestion('Server name', 'web-1')
        ->expectsQuestion('Server type', 'cx11')
        ->expectsQuestion('Image', 'ubuntu-22.04')
        ->expectsQuestion('Region', 'fsn1')
        ->expectsOutputToContain('Server [web-1] created successfully')
        ->assertSuccessful();

    $this->assertDatabaseHas('servers', [
        'name' => 'web-1',
        'external_id' => '456',
        'cloud_provider_id' => $provider->id,
    ]);
});

test('create server command fails when not authenticated', function (): void {
    $this->artisan('server:create')
        ->expectsOutputToContain('You are not logged in')
        ->assertFailed();
});

test('create server command shows message when no providers', function (): void {
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

    $this->artisan('server:create')
        ->expectsOutputToContain('No cloud providers configured')
        ->assertSuccessful();
});

test('create server command shows message when no infrastructures', function (): void {
    $mockServerService = Mockery::mock(ServerService::class);

    $mockFactory = Mockery::mock(CloudProviderFactory::class);
    $mockFactory->shouldReceive('makeServerService')
        ->never();
    $this->app->instance(CloudProviderFactory::class, $mockFactory);

    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    $provider = CloudProvider::factory()->hetzner()->create([
        'organization_id' => $organization->id,
        'name' => 'Hetzner Prod',
    ]);

    $userData = new LoginUser()->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    $this->artisan('server:create')
        ->expectsQuestion('Select a cloud provider', $provider->id)
        ->expectsOutputToContain('No infrastructures configured')
        ->assertSuccessful();
});

test('create server command fails when api throws exception', function (): void {
    $mockServerService = Mockery::mock(ServerService::class);
    $mockServerService->shouldReceive('create')
        ->once()
        ->andThrow(new RuntimeException('API error'));

    $mockFactory = Mockery::mock(CloudProviderFactory::class);
    $mockFactory->shouldReceive('makeServerService')
        ->once()
        ->andReturn($mockServerService);
    $this->app->instance(CloudProviderFactory::class, $mockFactory);

    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    $provider = CloudProvider::factory()->hetzner()->create([
        'organization_id' => $organization->id,
        'name' => 'Hetzner Prod',
    ]);

    $infrastructure = Infrastructure::factory()->create([
        'organization_id' => $organization->id,
        'cloud_provider_id' => $provider->id,
    ]);

    $userData = new LoginUser()->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    $this->artisan('server:create')
        ->expectsQuestion('Select a cloud provider', $provider->id)
        ->expectsQuestion('Select an infrastructure', $infrastructure->id)
        ->expectsQuestion('Server name', 'web-1')
        ->expectsQuestion('Server type', 'cx11')
        ->expectsQuestion('Image', 'ubuntu-22.04')
        ->expectsQuestion('Region', 'fsn1')
        ->expectsOutputToContain('API error')
        ->assertFailed();
});
