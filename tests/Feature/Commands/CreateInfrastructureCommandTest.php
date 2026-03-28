<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Client\InMemoryCloudProviderClient;
use App\Client\InMemoryInfrastructureClient;
use App\Console\Services\SessionManager;
use App\Contracts\CloudProviderClient;
use App\Contracts\InfrastructureClient;
use App\Data\CloudProviderData;
use App\Data\InfrastructureData;
use App\Data\SessionOrganizationData;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);
    $this->cloudProviderClient = new InMemoryCloudProviderClient();
    $this->app->instance(CloudProviderClient::class, $this->cloudProviderClient);
    $this->infrastructureClient = new InMemoryInfrastructureClient();
    $this->app->instance(InfrastructureClient::class, $this->infrastructureClient);
});

test('create infrastructure command creates infrastructure successfully',
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

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));

        $this->cloudProviderClient->setListResponse([
            new CloudProviderData(id: 'cp-1', name: 'Hetzner Prod', type: 'hetzner', isVerified: true),
        ]);

        $this->infrastructureClient->setCreateResponse(new InfrastructureData(
            id: 'infra-1',
            name: 'Production',
            description: 'Production infrastructure',
            status: 'healthy',
            cloudProviderId: 'cp-1',
        ));

        $this->artisan('infrastructure:create')
            ->expectsQuestion('Select a cloud provider', 'cp-1')
            ->expectsQuestion('Infrastructure name', 'Production')
            ->expectsQuestion('Description (optional)', 'Production infrastructure')
            ->expectsOutputToContain('Infrastructure [Production] created successfully')
            ->assertSuccessful();
    });

test('create infrastructure command fails when not authenticated',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('infrastructure:create')
            ->expectsOutputToContain('You are not logged in')
            ->assertFailed();
    });

test('create infrastructure command shows message when no providers',
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

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));

        $this->artisan('infrastructure:create')
            ->expectsOutputToContain('No cloud providers configured')
            ->assertSuccessful();
    });

test('create infrastructure command displays error on list providers failure',
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

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));

        $this->cloudProviderClient->shouldFailList();

        $this->artisan('infrastructure:create')
            ->expectsOutputToContain('Unauthenticated.')
            ->assertFailed();
    });

test('create infrastructure command displays error on create failure',
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

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));

        $this->cloudProviderClient->setListResponse([
            new CloudProviderData(id: 'cp-1', name: 'Hetzner Prod', type: 'hetzner', isVerified: true),
        ]);

        $this->infrastructureClient->shouldFailCreate();

        $this->artisan('infrastructure:create')
            ->expectsQuestion('Select a cloud provider', 'cp-1')
            ->expectsQuestion('Infrastructure name', 'Production')
            ->expectsQuestion('Description (optional)', '')
            ->expectsOutputToContain('Validation failed.')
            ->assertFailed();
    });

test('create infrastructure command works with provider CLI option',
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

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));

        $this->cloudProviderClient->setListResponse([
            new CloudProviderData(id: 'cp-1', name: 'Hetzner Prod', type: 'hetzner', isVerified: true),
        ]);

        $this->artisan('infrastructure:create --provider=cp-1 --name="Staging" --description="Staging infra"')
            ->expectsOutputToContain('Infrastructure [Staging] created successfully')
            ->assertSuccessful();
    });

test('create infrastructure command fails when provider not found from CLI option',
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

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));

        $this->cloudProviderClient->setListResponse([
            new CloudProviderData(id: 'cp-1', name: 'Hetzner Prod', type: 'hetzner', isVerified: true),
        ]);

        $this->artisan('infrastructure:create --provider=99999 --name="Test"')
            ->expectsOutputToContain('Provider not found.')
            ->assertFailed();
    });
