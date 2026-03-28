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

test('select infrastructure command selects infrastructure successfully',
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

        $this->infrastructureClient->setListResponse([
            new InfrastructureData(id: 'infra-1', name: 'Production', description: null, status: 'healthy', cloudProviderId: 'cp-1'),
        ]);

        $this->artisan('infrastructure:select')
            ->expectsQuestion('Select a cloud provider', 'cp-1')
            ->expectsQuestion('Select an infrastructure', 'infra-1')
            ->expectsOutputToContain('Selected infrastructure [Production]')
            ->assertSuccessful();

        expect($session->getInfrastructure())->not->toBeNull()
            ->and($session->getInfrastructure()?->id)->toBe('infra-1');
    });

test('select infrastructure command displays error on list providers failure',
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

        $this->artisan('infrastructure:select')
            ->expectsOutputToContain('Unauthenticated.')
            ->assertFailed();
    });

test('select infrastructure command displays error on list infra failure',
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
        $this->infrastructureClient->shouldFailList();

        $this->artisan('infrastructure:select')
            ->expectsQuestion('Select a cloud provider', 'cp-1')
            ->expectsOutputToContain('Unauthenticated.')
            ->assertFailed();
    });

test('select infrastructure command fails when no providers',
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

        $this->artisan('infrastructure:select')
            ->expectsOutputToContain('No cloud providers configured')
            ->assertFailed();
    });

test('select infrastructure command fails when no infrastructures',
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

        $this->artisan('infrastructure:select')
            ->expectsQuestion('Select a cloud provider', 'cp-1')
            ->expectsOutputToContain('No infrastructures configured')
            ->assertFailed();
    });

test('select infrastructure command fails when not authenticated',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('infrastructure:select')
            ->expectsOutputToContain('You are not logged in')
            ->assertFailed();
    });

test('select infrastructure command fails when no organization selected',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);

        $this->artisan('infrastructure:select')
            ->expectsOutputToContain('No organization selected')
            ->assertFailed();
    });
