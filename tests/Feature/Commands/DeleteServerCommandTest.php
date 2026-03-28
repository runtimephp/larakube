<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Data\ServerData;
use App\Data\SessionOrganizationData;
use App\Enums\ServerStatus;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\Server;
use App\Models\User;
use App\Services\InMemory\InMemoryHetznerServerService;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);

    /** @var LoginUser $this->loginUser */
    $this->loginUser = app(LoginUser::class);
});

test('delete server removes server successfully',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var InMemoryHetznerServerService $serverService */
        $serverService = useInMemoryHetznerServerService();
        $serverService->addServer(new ServerData(
            externalId: '123',
            name: 'web-1',
            status: ServerStatus::Running,
            type: 'cx11',
            region: 'fsn1',
            ipv4: '1.2.3.4',
        ));

        bindInMemoryHetznerFactory(serverService: $serverService);

        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);

        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->hetzner()->create([
            'organization_id' => $organization->id,
            'name' => 'Hetzner Prod',
        ]);

        /** @var Server $server */
        $server = Server::factory()->create([
            'organization_id' => $organization->id,
            'cloud_provider_id' => $provider->id,
            'name' => 'web-1',
            'external_id' => '123',
        ]);

        $userData = $this->loginUser->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));

        $this->artisan('server:delete')
            ->expectsQuestion('Select a cloud provider', $provider->id)
            ->expectsQuestion('Select a server to delete', $server->id)
            ->expectsConfirmation("Are you sure you want to delete [{$server->name}]?", 'yes')
            ->expectsOutputToContain('Server [web-1] deleted')
            ->assertSuccessful();

        $this->assertDatabaseMissing('servers', ['id' => $server->id]);
    });

test('delete server shows message when no servers',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var InMemoryHetznerServerService $serverService */
        $serverService = useInMemoryHetznerServerService();

        bindInMemoryHetznerFactory(serverService: $serverService);

        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);

        CloudProvider::factory()->hetzner()->create([
            'organization_id' => $organization->id,
            'name' => 'Hetzner Prod',
        ]);

        $userData = $this->loginUser->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));

        /** @var CloudProvider $provider */
        $provider = $organization->cloudProviders->first();

        $this->artisan('server:delete')
            ->expectsQuestion('Select a cloud provider', $provider->id)
            ->expectsOutputToContain('No servers to delete')
            ->assertSuccessful();
    });

test('delete server cancels when user declines confirmation',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var InMemoryHetznerServerService $serverService */
        $serverService = useInMemoryHetznerServerService();
        $serverService->addServer(new ServerData(
            externalId: '123',
            name: 'web-1',
            status: ServerStatus::Running,
            type: 'cx11',
            region: 'fsn1',
            ipv4: '1.2.3.4',
        ));

        bindInMemoryHetznerFactory(serverService: $serverService);

        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);

        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->hetzner()->create([
            'organization_id' => $organization->id,
            'name' => 'Hetzner Prod',
        ]);

        /** @var Server $server */
        $server = Server::factory()->create([
            'organization_id' => $organization->id,
            'cloud_provider_id' => $provider->id,
            'name' => 'web-1',
            'external_id' => '123',
        ]);

        $userData = $this->loginUser->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));

        $this->artisan('server:delete')
            ->expectsQuestion('Select a cloud provider', $provider->id)
            ->expectsQuestion('Select a server to delete', $server->id)
            ->expectsConfirmation("Are you sure you want to delete [{$server->name}]?", 'no')
            ->expectsOutputToContain('Cancelled')
            ->assertSuccessful();

        $this->assertDatabaseHas('servers', ['id' => $server->id]);
    });

test('delete server shows error when api call fails',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var InMemoryHetznerServerService $serverService */
        $serverService = useInMemoryHetznerServerService();
        $serverService->addServer(new ServerData(
            externalId: '123',
            name: 'web-1',
            status: ServerStatus::Running,
            type: 'cx11',
            region: 'fsn1',
            ipv4: '1.2.3.4',
        ));
        $serverService->shouldFailDelete(true);

        bindInMemoryHetznerFactory(serverService: $serverService);

        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);

        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->hetzner()->create([
            'organization_id' => $organization->id,
            'name' => 'Hetzner Prod',
        ]);

        /** @var Server $server */
        $server = Server::factory()->create([
            'organization_id' => $organization->id,
            'cloud_provider_id' => $provider->id,
            'name' => 'web-1',
            'external_id' => '123',
        ]);

        $userData = $this->loginUser->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));

        $this->artisan('server:delete')
            ->expectsQuestion('Select a cloud provider', $provider->id)
            ->expectsQuestion('Select a server to delete', $server->id)
            ->expectsConfirmation("Are you sure you want to delete [{$server->name}]?", 'yes')
            ->expectsOutputToContain('Failed to delete server')
            ->assertFailed();

        $this->assertDatabaseHas('servers', ['id' => $server->id]);
    });
