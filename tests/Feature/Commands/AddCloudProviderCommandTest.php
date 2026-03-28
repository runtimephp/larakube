<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Client\InMemoryCloudProviderClient;
use App\Console\Services\SessionManager;
use App\Contracts\CloudProviderClient;
use App\Data\CloudProviderData;
use App\Data\SessionOrganizationData;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);
    $this->cloudProviderClient = new InMemoryCloudProviderClient();
    $this->app->instance(CloudProviderClient::class, $this->cloudProviderClient);
});

test('add cloud provider command creates provider with valid token',
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

        $this->cloudProviderClient->setCreateResponse(new CloudProviderData(
            id: 'uuid-cp-1',
            name: 'Hetzner Production',
            type: 'hetzner',
            isVerified: true,
        ));

        $this->artisan('cloud-provider:add')
            ->expectsQuestion('Select a cloud provider', 'hetzner')
            ->expectsQuestion('Name for this provider', 'Hetzner Production')
            ->expectsQuestion('API token', 'valid-token')
            ->expectsOutputToContain('Cloud provider [Hetzner Production] added successfully')
            ->assertSuccessful();
    });

test('add cloud provider command fails with invalid token',
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

        $this->cloudProviderClient->shouldFailCreate();

        $this->artisan('cloud-provider:add')
            ->expectsQuestion('Select a cloud provider', 'hetzner')
            ->expectsQuestion('Name for this provider', 'Hetzner Staging')
            ->expectsQuestion('API token', 'invalid-token')
            ->expectsOutputToContain('The API token for Hetzner is invalid')
            ->assertFailed();
    });

test('add cloud provider command fails when not authenticated',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('cloud-provider:add')
            ->expectsOutputToContain('You are not logged in')
            ->assertFailed();
    });

test('add cloud provider command fails with invalid provider type from CLI option',
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

        $this->artisan('cloud-provider:add --type=invalid-type --name="Test" --token="token"')
            ->expectsOutputToContain('Invalid provider type. Use: hetzner, digitalocean, multipass')
            ->assertFailed();
    });

test('add multipass provider skips token prompt',
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

        $this->cloudProviderClient->setCreateResponse(new CloudProviderData(
            id: 'uuid-mp-1',
            name: 'Local Multipass',
            type: 'multipass',
            isVerified: true,
        ));

        $this->artisan('cloud-provider:add --type=multipass --name="Local Multipass"')
            ->expectsOutputToContain('Checking Multipass installation')
            ->expectsOutputToContain('Cloud provider [Local Multipass] added successfully')
            ->assertSuccessful();
    });
