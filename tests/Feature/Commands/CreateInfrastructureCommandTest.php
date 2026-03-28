<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Data\SessionOrganizationData;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);
});

test('create infrastructure command creates infrastructure successfully',
    /**
     * @throws Throwable
     */
    function (): void {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);

        $provider = CloudProvider::factory()->hetzner()->create([
            'organization_id' => $organization->id,
            'name' => 'Hetzner Prod',
        ]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));

        $this->artisan('infrastructure:create')
            ->expectsQuestion('Select a cloud provider', $provider->id)
            ->expectsQuestion('Infrastructure name', 'Production')
            ->expectsQuestion('Description (optional)', 'Production infrastructure')
            ->expectsOutputToContain('Infrastructure [Production] created successfully')
            ->assertSuccessful();

        $this->assertDatabaseHas('infrastructures', [
            'name' => 'Production',
            'description' => 'Production infrastructure',
            'cloud_provider_id' => $provider->id,
        ]);
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
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

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

test('create infrastructure command fails when provider not found from CLI option',
    /**
     * @throws Throwable
     */
    function (): void {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);

        $provider = CloudProvider::factory()->hetzner()->create([
            'organization_id' => $organization->id,
            'name' => 'Hetzner Prod',
        ]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));

        $this->artisan('infrastructure:create --provider=99999 --name="Test"')
            ->expectsOutputToContain('Provider not found.')
            ->assertFailed();
    });
