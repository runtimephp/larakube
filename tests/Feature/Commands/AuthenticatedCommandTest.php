<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Client\InMemoryOrganizationClient;
use App\Console\Services\SessionManager;
use App\Contracts\OrganizationClient;
use App\Data\OrganizationData;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);
    $this->organizationClient = new InMemoryOrganizationClient();
    $this->app->instance(OrganizationClient::class, $this->organizationClient);
});

test('authenticated command blocks unauthenticated users',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('organization:select')
            ->expectsOutputToContain('You are not logged in')
            ->assertFailed();
    });

test('authenticated command allows authenticated users',
    /**
     * @throws Throwable
     */
    function (): void {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization, ['role' => 'owner']);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);

        $this->organizationClient->setListResponse([
            new OrganizationData(id: $organization->id, name: $organization->name, slug: $organization->slug),
        ]);

        $this->artisan('organization:select')
            ->expectsQuestion('Select an organization', $organization->id)
            ->assertSuccessful();
    });

test('authenticated command detects corrupted session',
    /**
     * @throws Throwable
     */
    function (): void {
        $session = app(SessionManager::class);
        $session->set('token', 'some-token');

        $this->artisan('organization:select')
            ->expectsOutputToContain('Session is corrupted')
            ->assertFailed();
    });

test('authenticated command fails when no organization selected',
    /**
     * @throws Throwable
     */
    function (): void {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);

        $this->artisan('cloud-provider:list')
            ->expectsOutputToContain('No organization selected')
            ->assertFailed();
    });

test('authenticated command detects expired token',
    /**
     * @throws Throwable
     */
    function (): void {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);

        // Revoke all tokens to simulate expiry
        $user->tokens()->delete();

        $this->artisan('organization:select')
            ->expectsOutputToContain('Your session has expired')
            ->assertFailed();
    });
