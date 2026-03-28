<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Data\ServerData;
use App\Data\SessionOrganizationData;
use App\Enums\ServerStatus;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);

    /** @var LoginUser $this->loginUser */
    $this->loginUser = app(LoginUser::class);
});

test('list servers syncs and displays table',
    /**
     * @throws Throwable
     */
    function (): void {
        $serverService = useInMemoryHetznerServerService();
        $serverService->addServer(new ServerData(
            externalId: 123,
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

        $userData = $this->loginUser->handle('john@example.com', 'password123');
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

test('list servers shows message when no providers',
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

        $userData = $this->loginUser->handle('john@example.com', 'password123');
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

test('list servers fails when not authenticated',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('server:list')
            ->expectsOutputToContain('You are not logged in')
            ->assertFailed();
    });

test('list servers shows message when no servers found',
    /**
     * @throws Throwable
     */
    function (): void {
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

        $userData = $this->loginUser->handle('john@example.com', 'password123');
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
