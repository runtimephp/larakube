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

test('create organization command creates org and auto-selects it',
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

        $this->organizationClient->setCreateResponse(new OrganizationData(
            id: 'uuid-org-1',
            name: 'Acme Corp',
            slug: 'acme-corp',
            description: 'A great company',
        ));

        $this->artisan('organization:create')
            ->expectsQuestion('Organization name', 'Acme Corp')
            ->expectsQuestion('Description', 'A great company')
            ->expectsOutputToContain('Organization [Acme Corp] created and selected')
            ->assertSuccessful();

        expect($session->hasOrganization())->toBeTrue()
            ->and($session->getOrganization()->name)->toBe('Acme Corp')
            ->and($session->getOrganization()->id)->toBe('uuid-org-1');
    });

test('create organization command displays error on failure',
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

        $this->organizationClient->shouldFailCreate();

        $this->artisan('organization:create')
            ->expectsQuestion('Organization name', 'Acme Corp')
            ->expectsQuestion('Description', '')
            ->expectsOutputToContain('Validation failed.')
            ->assertFailed();
    });

test('create organization command fails when not authenticated',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('organization:create')
            ->expectsOutputToContain('You are not logged in')
            ->assertFailed();
    });
