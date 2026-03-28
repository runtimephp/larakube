<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Client\InMemoryOrganizationClient;
use App\Client\InMemoryServerClient;
use App\Console\Services\SessionManager;
use App\Contracts\OrganizationClient;
use App\Contracts\ServerClient;
use App\Data\SessionInfrastructureData;
use App\Data\SessionOrganizationData;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);
    $this->organizationClient = new InMemoryOrganizationClient();
    $this->app->instance(OrganizationClient::class, $this->organizationClient);
    $this->serverClient = new InMemoryServerClient();
    $this->app->instance(ServerClient::class, $this->serverClient);
});

function setupAuthenticatedSession(object $test): array
{
    $user = User::factory()->create([
        'email' => 'coverage@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    $userData = app(LoginUser::class)->handle('coverage@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    return [$user, $organization, $session];
}

function setupAuthenticatedSessionWithInfrastructure(object $test): array
{
    $user = User::factory()->create([
        'email' => 'coverage@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    $infrastructure = Infrastructure::factory()->create([
        'organization_id' => $organization->id,
    ]);

    $userData = app(LoginUser::class)->handle('coverage@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));
    $session->setInfrastructure(new SessionInfrastructureData(
        id: $infrastructure->id,
        name: $infrastructure->name,
    ));

    return [$user, $organization, $session];
}

// CreateServerCommand: success
// test('server:create shows message when no providers', function (): void {
//     [, $organization] = setupAuthenticatedSessionWithInfrastructure($this);
//
//     $provider = CloudProvider::factory()->hetzner()->create([
//         'organization_id' => $organization->id,
//     ]);
//
//     $this->artisan('server:create')
//         ->expectsQuestion('Select a cloud provider', $provider->id)
//         ->expectsOutputToContain('No cloud providers configured')
//         ->assertSuccessful();
// });

// ShowServerCommand: server not found
test('server:show shows error when server not found', function (): void {
    setupAuthenticatedSession($this);

    $this->serverClient->shouldFailShow();

    $this->artisan('server:show --id=nonexistent')
        ->expectsOutputToContain('Server not found.')
        ->assertFailed();
});

// DeleteServerCommand: no servers
test('server:delete shows message when no servers', function (): void {
    setupAuthenticatedSession($this);

    $this->artisan('server:delete')
        ->expectsOutputToContain('No servers to delete')
        ->assertSuccessful();
});

// DeleteServerCommand: api error on delete
// test('server:delete fails on api error', function (): void {
//     Http::fake();
//     [, $organization] = setupAuthenticatedSession($this);

//     $provider = CloudProvider::factory()->hetzner()->create([
//         'organization_id' => $organization->id,
//     ]);

//     $server = Server::factory()->create([
//         'organization_id' => $organization->id,
//         'cloud_provider_id' => $provider->id,
//         'name' => 'web-1',
//         'external_id' => '999',
//     ]);

//     $serverData = new App\Data\ServerData(
//         externalId: 999,
//         name: 'web-1',
//         status: App\Enums\ServerStatus::Running,
//         type: 'cx11',
//         region: 'fsn1',
//         ipv4: '1.2.3.4',
//     );

//     $mockServerService = Mockery::mock(ServerService::class);
//     $mockServerService->shouldReceive('getAll')->once()->andReturn(collect([$serverData]));
//     $mockServerService->shouldReceive('destroy')
//         ->once()
//         ->andReturnFalse();

//     $mockFactory = Mockery::mock(CloudProviderServiceFactory::class);
//     $mockFactory->shouldReceive('makeServerService')
//         ->twice()
//         ->andReturn($mockServerService);
//     $this->app->instance(CloudProviderServiceFactory::class, $mockFactory);

//     $this->artisan('server:delete')
//         ->expectsQuestion('Select a cloud provider', $provider->id)
//         ->expectsQuestion('Select a server to delete', $server->id)
//         ->expectsConfirmation("Are you sure you want to delete [{$server->name}]?", 'yes')
//         ->expectsOutputToContain('Failed to delete server')
//         ->assertFailed();
// });

// ListServersCommand: syncs but finds no servers
// test('server:list shows no servers message after sync', function (): void {
//     $mockServerService = Mockery::mock(ServerService::class);
//     $mockServerService->shouldReceive('getAll')->once()->andReturn(collect([]));

//     $mockFactory = Mockery::mock(CloudProviderServiceFactory::class);
//     $mockFactory->shouldReceive('makeServerService')
//         ->once()
//         ->andReturn($mockServerService);
//     $this->app->instance(CloudProviderServiceFactory::class, $mockFactory);

//     [, $organization] = setupAuthenticatedSession($this);

//     $provider = CloudProvider::factory()->hetzner()->create([
//         'organization_id' => $organization->id,
//     ]);

//     $this->artisan('server:list')
//         ->expectsQuestion('Select a cloud provider', $provider->id)
//         ->expectsOutputToContain('No servers found')
//         ->assertSuccessful();
// });

// CreateOrganizationCommand: error branch
test('organization:create handles creation failure', function (): void {
    setupAuthenticatedSession($this);

    $this->organizationClient->shouldFailCreate();

    $this->artisan('organization:create')
        ->expectsQuestion('Organization name', 'Duplicate')
        ->expectsQuestion('Description', '')
        ->expectsOutputToContain('Validation failed.')
        ->assertFailed();
});
