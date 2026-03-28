<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Data\SessionOrganizationData;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);

    /** @var LoginUser $this->loginUser */
    $this->loginUser = app(LoginUser::class);
});

test('add cloud provider command creates provider with valid token',
    /**
     * @throws Throwable
     */
    function (): void {
        $hetznerService = useInMemoryHetznerService(true);

        bindInMemoryHetznerFactory($hetznerService);

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

        $this->artisan('cloud-provider:add')
            ->expectsQuestion('Select a cloud provider', 'hetzner')
            ->expectsQuestion('Name for this provider', 'Hetzner Production')
            ->expectsQuestion('API token', 'valid-token')
            ->expectsOutputToContain('Cloud provider [Hetzner Production] added successfully')
            ->assertSuccessful();

        $this->assertDatabaseHas('cloud_providers', [
            'organization_id' => $organization->id,
            'name' => 'Hetzner Production',
            'type' => 'hetzner',
            'is_verified' => true,
        ]);
    });

test('add cloud provider command fails with invalid token',
    /**
     * @throws Throwable
     */
    function (): void {
        $hetznerService = useInMemoryHetznerService(false);

        bindInMemoryHetznerFactory($hetznerService);

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

        $userData = $this->loginUser->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));

        $this->artisan('cloud-provider:add --type=invalid-type --name="Test" --token="token"')
            ->expectsOutputToContain('Invalid provider type. Use: hetzner, digitalocean')
            ->assertFailed();
    });
