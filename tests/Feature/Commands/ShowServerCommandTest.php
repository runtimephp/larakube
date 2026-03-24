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
    $tempPath = sys_get_temp_dir().'/show-server-test-'.uniqid().'/session.json';
    $this->app->singleton(SessionManager::class, fn () => new SessionManager($tempPath));
});

test('show server displays server details', function (): void {
    $mockManager = Mockery::mock(ServerManagerInterface::class);
    $mockManager->shouldReceive('findByName')
        ->once()
        ->andReturn(new ServerData(
            externalId: 789,
            name: 'web-1',
            status: ServerStatus::Running,
            type: 'cx11',
            region: 'fsn1',
            ipv4: '1.2.3.4',
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

    $this->artisan('server:show')
        ->expectsQuestion('Select a cloud provider', $provider->id)
        ->expectsQuestion('Server name', 'web-1')
        ->expectsOutputToContain('web-1')
        ->assertSuccessful();
});

test('show server shows error when not found', function (): void {
    $mockManager = Mockery::mock(ServerManagerInterface::class);
    $mockManager->shouldReceive('findByName')
        ->once()
        ->andReturnNull();
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

    $this->artisan('server:show')
        ->expectsQuestion('Select a cloud provider', $provider->id)
        ->expectsQuestion('Server name', 'nonexistent')
        ->expectsOutputToContain('Server [nonexistent] not found')
        ->assertFailed();
});
