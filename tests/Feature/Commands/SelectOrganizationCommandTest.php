<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Client\InMemoryOrganizationClient;
use App\Console\Services\SessionManager;
use App\Contracts\OrganizationClient;
use App\Data\OrganizationData;
use App\Models\User;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);
    $this->organizationClient = new InMemoryOrganizationClient();
    $this->app->instance(OrganizationClient::class, $this->organizationClient);
});

test('select organization command lists and persists selection',
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

        $this->organizationClient->setListResponse([
            new OrganizationData(id: 'uuid-org-1', name: 'Acme Corp', slug: 'acme-corp'),
            new OrganizationData(id: 'uuid-org-2', name: 'Beta Inc', slug: 'beta-inc'),
        ]);

        $this->artisan('organization:select')
            ->expectsQuestion('Select an organization', 'uuid-org-1')
            ->expectsOutputToContain('Selected organization [Acme Corp]')
            ->assertSuccessful();

        expect($session->hasOrganization())->toBeTrue()
            ->and($session->getOrganization()->id)->toBe('uuid-org-1')
            ->and($session->getOrganization()->name)->toBe('Acme Corp');
    });

test('select organization command fails when user has no orgs',
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

        $this->artisan('organization:select')
            ->expectsOutputToContain('You do not belong to any organizations')
            ->assertFailed();
    });

test('select organization command displays error on api failure',
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

        $this->organizationClient->shouldFailList();

        $this->artisan('organization:select')
            ->expectsOutputToContain('Unauthenticated.')
            ->assertFailed();
    });

test('select organization command fails when not authenticated',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('organization:select')
            ->expectsOutputToContain('You are not logged in')
            ->assertFailed();
    });
