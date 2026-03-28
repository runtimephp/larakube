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

test('remove cloud provider deletes the selected provider',
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
            new CloudProviderData(id: 'uuid-cp-1', name: 'Hetzner Production', type: 'hetzner', isVerified: true),
        ]);

        $this->artisan('cloud-provider:remove')
            ->expectsQuestion('Select a cloud provider to remove', 'uuid-cp-1')
            ->expectsConfirmation('Are you sure you want to remove [Hetzner Production]?', 'yes')
            ->expectsOutputToContain('Cloud provider [Hetzner Production] removed')
            ->assertSuccessful();

        expect($this->cloudProviderClient->deleteCalled)->toBeTrue()
            ->and($this->cloudProviderClient->deletedId)->toBe('uuid-cp-1');
    });

test('remove cloud provider shows message when none exist',
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

        $this->artisan('cloud-provider:remove')
            ->expectsOutputToContain('No cloud providers to remove')
            ->assertSuccessful();
    });

test('remove cloud provider cancels when user declines confirmation',
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
            new CloudProviderData(id: 'uuid-cp-1', name: 'Hetzner Production', type: 'hetzner', isVerified: true),
        ]);

        $this->artisan('cloud-provider:remove')
            ->expectsQuestion('Select a cloud provider to remove', 'uuid-cp-1')
            ->expectsConfirmation('Are you sure you want to remove [Hetzner Production]?', 'no')
            ->expectsOutputToContain('Cancelled')
            ->assertSuccessful();

        expect($this->cloudProviderClient->deleteCalled)->toBeFalse();
    });

test('remove cloud provider displays error on list failure',
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

        $this->artisan('cloud-provider:remove')
            ->expectsOutputToContain('Unauthenticated.')
            ->assertFailed();
    });

test('remove cloud provider displays error on delete failure',
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
            new CloudProviderData(id: 'uuid-cp-1', name: 'Hetzner Production', type: 'hetzner', isVerified: true),
        ]);
        $this->cloudProviderClient->shouldFailDelete();

        $this->artisan('cloud-provider:remove')
            ->expectsQuestion('Select a cloud provider to remove', 'uuid-cp-1')
            ->expectsConfirmation('Are you sure you want to remove [Hetzner Production]?', 'yes')
            ->expectsOutputToContain('Failed to delete cloud provider.')
            ->assertFailed();
    });
