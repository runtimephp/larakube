<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Data\SessionInfrastructureData;
use App\Data\SessionOrganizationData;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\User;
use App\Services\InMemory\InMemoryHetznerServerService;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);

    /** @var LoginUser $this->loginUser */
    $this->loginUser = app(LoginUser::class);
});

test('create server command creates server successfully',
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

        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->hetzner()->create([
            'organization_id' => $organization->id,
            'name' => 'Hetzner Prod',
        ]);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create([
            'organization_id' => $organization->id,
            'cloud_provider_id' => $provider->id,
        ]);

        $userData = $this->loginUser->handle('john@example.com', 'password123');
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
            'cloud_provider_id' => $provider->id,
        ]);
    });

test('create server command fails when not authenticated',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('server:create')
            ->expectsOutputToContain('You are not logged in')
            ->assertFailed();
    });

test('create server command shows message when no providers',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $userData = $this->loginUser->handle('john@example.com', 'password123');
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

        $this->artisan('server:create')
            ->expectsOutputToContain('No cloud providers configured')
            ->assertSuccessful();
    });

test('create server command fails when no infrastructure selected',
    /**
     * @throws Throwable
     */
    function (): void {
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

        $this->artisan('server:create')
            ->expectsOutputToContain('No infrastructure selected')
            ->assertFailed();
    });

test('create server command fails when api throws exception',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var InMemoryHetznerServerService $serverService */
        $serverService = useInMemoryHetznerServerService();
        $serverService->shouldFailCreate(true);

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

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create([
            'organization_id' => $organization->id,
            'cloud_provider_id' => $provider->id,
        ]);

        $userData = $this->loginUser->handle('john@example.com', 'password123');
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

        $this->artisan('server:create')
            ->expectsQuestion('Select a cloud provider', $provider->id)
            ->expectsQuestion('Server name', 'web-1')
            ->expectsQuestion('Server type', 'cx11')
            ->expectsQuestion('Image', 'ubuntu-22.04')
            ->expectsQuestion('Region', 'fsn1')
            ->expectsOutputToContain('Simulated API failure on create')
            ->assertFailed();
    });
