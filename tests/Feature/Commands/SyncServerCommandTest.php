<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Client\InMemoryCloudProviderClient;
use App\Client\InMemoryServerClient;
use App\Console\Services\SessionManager;
use App\Contracts\CloudProviderClient;
use App\Contracts\ServerClient;
use App\Data\CloudProviderData;
use App\Data\SessionInfrastructureData;
use App\Data\SessionOrganizationData;
use App\Data\SyncSummaryData;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);
    $this->cloudProviderClient = new InMemoryCloudProviderClient();
    $this->app->instance(CloudProviderClient::class, $this->cloudProviderClient);
    $this->serverClient = new InMemoryServerClient();
    $this->app->instance(ServerClient::class, $this->serverClient);
});

test('sync server command displays summary',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create(['email' => 'john@example.com', 'password' => 'password123']);
        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create(['organization_id' => $organization->id]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(id: $organization->id, name: $organization->name, slug: $organization->slug));
        $session->setInfrastructure(new SessionInfrastructureData(id: $infrastructure->id, name: $infrastructure->name));

        $this->cloudProviderClient->setListResponse([
            new CloudProviderData(id: 'cp-1', name: 'Hetzner Prod', type: 'hetzner', isVerified: true),
        ]);

        $this->serverClient->setSyncResponse(new SyncSummaryData(created: 3, updated: 1, deleted: 2));

        $this->artisan('server:sync')
            ->expectsQuestion('Select a cloud provider', 'cp-1')
            ->expectsOutputToContain('3 created, 1 updated, 2 deleted')
            ->assertSuccessful();
    });

test('sync server command works with provider flag',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create(['email' => 'john@example.com', 'password' => 'password123']);
        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create(['organization_id' => $organization->id]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(id: $organization->id, name: $organization->name, slug: $organization->slug));
        $session->setInfrastructure(new SessionInfrastructureData(id: $infrastructure->id, name: $infrastructure->name));

        $this->cloudProviderClient->setListResponse([
            new CloudProviderData(id: 'cp-1', name: 'Hetzner Prod', type: 'hetzner', isVerified: true),
        ]);

        $this->artisan('server:sync --provider=cp-1')
            ->expectsOutputToContain('Sync complete')
            ->assertSuccessful();
    });

test('sync server command fails when sync api errors',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create(['email' => 'john@example.com', 'password' => 'password123']);
        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create(['organization_id' => $organization->id]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(id: $organization->id, name: $organization->name, slug: $organization->slug));
        $session->setInfrastructure(new SessionInfrastructureData(id: $infrastructure->id, name: $infrastructure->name));

        $this->cloudProviderClient->setListResponse([
            new CloudProviderData(id: 'cp-1', name: 'Hetzner Prod', type: 'hetzner', isVerified: true),
        ]);
        $this->serverClient->shouldFailSync();

        $this->artisan('server:sync')
            ->expectsQuestion('Select a cloud provider', 'cp-1')
            ->expectsOutputToContain('Failed to sync servers.')
            ->assertFailed();
    });

test('sync server command fails on list providers error',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create(['email' => 'john@example.com', 'password' => 'password123']);
        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create(['organization_id' => $organization->id]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(id: $organization->id, name: $organization->name, slug: $organization->slug));
        $session->setInfrastructure(new SessionInfrastructureData(id: $infrastructure->id, name: $infrastructure->name));

        $this->cloudProviderClient->shouldFailList();

        $this->artisan('server:sync')
            ->expectsOutputToContain('Unauthenticated.')
            ->assertFailed();
    });

test('sync server command shows message when no providers',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create(['email' => 'john@example.com', 'password' => 'password123']);
        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create(['organization_id' => $organization->id]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(id: $organization->id, name: $organization->name, slug: $organization->slug));
        $session->setInfrastructure(new SessionInfrastructureData(id: $infrastructure->id, name: $infrastructure->name));

        $this->artisan('server:sync')
            ->expectsOutputToContain('No cloud providers configured')
            ->assertSuccessful();
    });

test('sync server command fails when not authenticated',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('server:sync')
            ->expectsOutputToContain('You are not logged in')
            ->assertFailed();
    });

test('sync server command fails with invalid provider flag',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create(['email' => 'john@example.com', 'password' => 'password123']);
        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create(['organization_id' => $organization->id]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(id: $organization->id, name: $organization->name, slug: $organization->slug));
        $session->setInfrastructure(new SessionInfrastructureData(id: $infrastructure->id, name: $infrastructure->name));

        $this->cloudProviderClient->setListResponse([
            new CloudProviderData(id: 'cp-1', name: 'Hetzner Prod', type: 'hetzner', isVerified: true),
        ]);

        $this->artisan('server:sync --provider=nonexistent')
            ->expectsOutputToContain('Provider not found.')
            ->assertFailed();
    });
