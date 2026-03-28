<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Contracts\ServerService;
use App\Data\ServerData;
use App\Data\SessionOrganizationData;
use App\Enums\ServerStatus;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\User;
use App\Services\CloudProviderFactory;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);
});

test('list servers syncs and displays table', function (): void {
    $mockServerService = Mockery::mock(ServerService::class);
    $mockServerService->shouldReceive('getAll')
        ->once()
        ->andReturn(collect([
            new ServerData(
                externalId: 123,
                name: 'web-1',
                status: ServerStatus::Running,
                type: 'cx11',
                region: 'fsn1',
                ipv4: '1.2.3.4',
            ),
        ]));

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

    $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    $this->artisan('server:list')
        ->expectsQuestion('Select a cloud provider', $provider->id)
        ->expectsOutputToContain('web-1')
        ->assertSuccessful();
});

test('list servers shows message when no providers', function (): void {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    $this->artisan('server:list')
        ->expectsOutputToContain('No cloud providers configured')
        ->assertSuccessful();
});

test('list servers fails when not authenticated', function (): void {
    $this->artisan('server:list')
        ->expectsOutputToContain('You are not logged in')
        ->assertFailed();
});

test('list servers shows message when no servers found', function (): void {
    $mockServerService = Mockery::mock(ServerService::class);
    $mockServerService->shouldReceive('getAll')
        ->once()
        ->andReturn(collect([]));

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

    $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    $this->artisan('server:list')
        ->expectsQuestion('Select a cloud provider', $provider->id)
        ->expectsOutputToContain('No servers found')
        ->assertSuccessful();
});
