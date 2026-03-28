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

test('create server command creates server successfully',
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
        $infrastructure = Infrastructure::factory()->create(['organization_id' => $organization->id]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(id: $organization->id, name: $organization->name, slug: $organization->slug));
        $session->setInfrastructure(new SessionInfrastructureData(id: $infrastructure->id, name: $infrastructure->name));

        $this->cloudProviderClient->setListResponse([
            new CloudProviderData(id: 'cp-1', name: 'Hetzner Prod', type: 'hetzner', isVerified: true),
        ]);

        $this->artisan('server:create')
            ->expectsQuestion('Select a cloud provider', 'cp-1')
            ->expectsQuestion('Server name', 'web-1')
            ->expectsQuestion('Server type', 'cx11')
            ->expectsQuestion('Image', 'ubuntu-22.04')
            ->expectsQuestion('Region', 'fsn1')
            ->expectsOutputToContain('Server [web-1] created successfully')
            ->assertSuccessful();
    });

test('create server command displays error on list providers failure',
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

        $this->artisan('server:create')
            ->expectsOutputToContain('Unauthenticated.')
            ->assertFailed();
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
        $user = User::factory()->create(['email' => 'john@example.com', 'password' => 'password123']);
        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(id: $organization->id, name: $organization->name, slug: $organization->slug));

        $this->artisan('server:create')
            ->expectsOutputToContain('No infrastructure selected')
            ->assertFailed();
    });

test('create server command fails when api throws exception',
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
        $this->serverClient->shouldFailCreate();

        $this->artisan('server:create')
            ->expectsQuestion('Select a cloud provider', 'cp-1')
            ->expectsQuestion('Server name', 'web-1')
            ->expectsQuestion('Server type', 'cx11')
            ->expectsQuestion('Image', 'ubuntu-22.04')
            ->expectsQuestion('Region', 'fsn1')
            ->expectsOutputToContain('Failed to create server.')
            ->assertFailed();
    });
