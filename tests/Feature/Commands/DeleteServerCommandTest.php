<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Client\InMemoryServerClient;
use App\Console\Services\SessionManager;
use App\Contracts\ServerClient;
use App\Data\ServerResourceData;
use App\Data\SessionOrganizationData;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);
    $this->serverClient = new InMemoryServerClient();
    $this->app->instance(ServerClient::class, $this->serverClient);
});

test('delete server removes server successfully',
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

        $this->serverClient->setListResponse([
            new ServerResourceData(id: 'srv-1', name: 'web-1', status: 'running', type: 'cx11', region: 'fsn1', ipv4: '1.2.3.4', ipv6: null, externalId: '123', cloudProviderId: 'cp-1', infrastructureId: 'infra-1'),
        ]);

        $this->artisan('server:delete')
            ->expectsQuestion('Select a server to delete', 'srv-1')
            ->expectsConfirmation('Are you sure you want to delete [web-1]?', 'yes')
            ->expectsOutputToContain('Server [web-1] deleted')
            ->assertSuccessful();

        expect($this->serverClient->deleteCalled)->toBeTrue()
            ->and($this->serverClient->deletedId)->toBe('srv-1');
    });

test('delete server shows message when no servers',
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

        $this->artisan('server:delete')
            ->expectsOutputToContain('No servers to delete')
            ->assertSuccessful();
    });

test('delete server cancels when user declines confirmation',
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

        $this->serverClient->setListResponse([
            new ServerResourceData(id: 'srv-1', name: 'web-1', status: 'running', type: 'cx11', region: 'fsn1', ipv4: '1.2.3.4', ipv6: null, externalId: '123', cloudProviderId: 'cp-1', infrastructureId: 'infra-1'),
        ]);

        $this->artisan('server:delete')
            ->expectsQuestion('Select a server to delete', 'srv-1')
            ->expectsConfirmation('Are you sure you want to delete [web-1]?', 'no')
            ->expectsOutputToContain('Cancelled')
            ->assertSuccessful();

        expect($this->serverClient->deleteCalled)->toBeFalse();
    });

test('delete server shows error when api call fails',
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

        $this->serverClient->setListResponse([
            new ServerResourceData(id: 'srv-1', name: 'web-1', status: 'running', type: 'cx11', region: 'fsn1', ipv4: '1.2.3.4', ipv6: null, externalId: '123', cloudProviderId: 'cp-1', infrastructureId: 'infra-1'),
        ]);
        $this->serverClient->shouldFailDelete();

        $this->artisan('server:delete')
            ->expectsQuestion('Select a server to delete', 'srv-1')
            ->expectsConfirmation('Are you sure you want to delete [web-1]?', 'yes')
            ->expectsOutputToContain('Failed to delete server.')
            ->assertFailed();
    });

test('delete server displays error on list failure',
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

        $this->serverClient->shouldFailList();

        $this->artisan('server:delete')
            ->expectsOutputToContain('Unauthenticated.')
            ->assertFailed();
    });
