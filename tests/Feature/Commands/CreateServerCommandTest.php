<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Contracts\ServerManagerInterface;
use App\Data\ServerData;
use App\Data\SessionOrganizationData;
use App\Enums\ServerStatus;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $tempPath = sys_get_temp_dir().'/create-server-test-'.uniqid().'/session.json';
    $this->app->singleton(SessionManager::class, fn () => new SessionManager($tempPath));
});

test('create server command creates server successfully', function (): void {
    $mockManager = Mockery::mock(ServerManagerInterface::class);
    $mockManager->shouldReceive('create')
        ->once()
        ->andReturn(new ServerData(
            externalId: 456,
            name: 'web-1',
            status: ServerStatus::Starting,
            type: 'cx11',
            region: 'fsn1',
            ipv4: null,
        ));
    $this->app->instance(ServerManagerInterface::class, $mockManager);

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
