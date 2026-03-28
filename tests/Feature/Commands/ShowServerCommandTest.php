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

test('show server displays server details',
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

        $this->serverClient->setShowResponse(new ServerResourceData(
            id: 'srv-1', name: 'web-1', status: 'running', type: 'cx11', region: 'fsn1',
            ipv4: '1.2.3.4', ipv6: null, externalId: '789', cloudProviderId: 'cp-1', infrastructureId: 'infra-1',
        ));

        $this->artisan('server:show --id=srv-1')
            ->expectsOutputToContain('web-1')
            ->assertSuccessful();
    });

test('show server shows error when not found',
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

        $this->serverClient->shouldFailShow();

        $this->artisan('server:show --id=nonexistent')
            ->expectsOutputToContain('Server not found.')
            ->assertFailed();
    });
