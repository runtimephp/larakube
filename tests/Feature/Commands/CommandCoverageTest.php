<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Data\SessionOrganizationData;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $tempPath = sys_get_temp_dir().'/cmd-coverage-test-'.uniqid().'/session.json';
    $this->app->singleton(SessionManager::class, fn () => new SessionManager($tempPath));
});

function setupAuthenticatedSession(object $test): array
{
    $user = User::factory()->create([
        'email' => 'coverage@example.com',
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user, ['role' => 'owner']);

    $userData = (new LoginUser)->handle('coverage@example.com', 'password123');
    $session = app(SessionManager::class);
    $session->setUser($userData);
    $session->setOrganization(new SessionOrganizationData(
        id: $organization->id,
        name: $organization->name,
        slug: $organization->slug,
    ));

    return [$user, $organization, $session];
}

// CreateServerCommand: no providers
test('server:create shows message when no providers', function (): void {
    setupAuthenticatedSession($this);

    $this->artisan('server:create')
        ->expectsOutputToContain('No cloud providers configured')
        ->assertSuccessful();
});

// CreateServerCommand: no infrastructures
test('server:create shows message when no infrastructures', function (): void {
    [, $organization] = setupAuthenticatedSession($this);

    $provider = CloudProvider::factory()->hetzner()->create([
        'organization_id' => $organization->id,
    ]);

    $this->artisan('server:create')
        ->expectsQuestion('Select a cloud provider', $provider->id)
        ->expectsOutputToContain('No infrastructures configured')
        ->assertSuccessful();
});

// CreateServerCommand: api error
// test('server:create fails on api error', function (): void {
//     $mockServerService = Mockery::mock(ServerService::class);
//     $mockServerService->shouldReceive('create')
//         ->once()
//         ->andThrow(new RuntimeException('API connection failed'));
//
//     $mockFactory = Mockery::mock(CloudProviderServiceFactory::class);
//     $mockFactory->shouldReceive('makeServerService')
//         ->once()
//         ->andReturn($mockServerService);
//     $this->app->instance(CloudProviderServiceFactory::class, $mockFactory);
//
//     [, $organization] = setupAuthenticatedSession($this);
//
//     $provider = CloudProvider::factory()->hetzner()->create([
//         'organization_id' => $organization->id,
//     ]);
//
//     $infrastructure = Infrastructure::factory()->create([
//         'organization_id' => $organization->id,
//         'cloud_provider_id' => $provider->id,
//     ]);
//
//     $this->artisan('server:create')
//         ->expectsQuestion('Select a cloud provider', $provider->id)
//         ->expectsQuestion('Select an infrastructure', $infrastructure->id)
//         ->expectsQuestion('Server name', 'web-1')
//         ->expectsQuestion('Server type', 'cx11')
//         ->expectsQuestion('Image', 'ubuntu-22.04')
//         ->expectsQuestion('Region', 'fsn1')
//         ->expectsOutputToContain('API connection failed')
//         ->assertFailed();
// });

// ShowServerCommand: no providers
test('server:show shows message when no providers', function (): void {
    setupAuthenticatedSession($this);

    $this->artisan('server:show')
        ->expectsOutputToContain('No cloud providers configured')
        ->assertSuccessful();
});

// DeleteServerCommand: no providers
test('server:delete shows message when no providers', function (): void {
    setupAuthenticatedSession($this);

    $this->artisan('server:delete')
        ->expectsOutputToContain('No cloud providers configured')
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
    [, $organization] = setupAuthenticatedSession($this);

    // Create org with same slug to trigger unique constraint
    Organization::query()->create(['name' => 'Duplicate', 'slug' => 'duplicate']);

    $this->artisan('organization:create')
        ->expectsQuestion('Organization name', 'Duplicate')
        ->expectsQuestion('Description', '')
        ->expectsOutputToContain('Failed to create organization')
        ->assertFailed();
});
