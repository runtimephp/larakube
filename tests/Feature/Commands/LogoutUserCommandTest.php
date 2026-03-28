<?php

declare(strict_types=1);

use App\Client\InMemoryAuthClient;
use App\Console\Services\SessionManager;
use App\Contracts\AuthClient;
use App\Data\SessionUserData;

beforeEach(function (): void {
    $this->authClient = new InMemoryAuthClient();
    $this->app->instance(AuthClient::class, $this->authClient);
    $this->app->singleton(SessionManager::class);
});

test('logout command clears session',
    /**
     * @throws Throwable
     */
    function (): void {
        $session = app(SessionManager::class);
        $session->setUser(new SessionUserData(
            id: 'uuid-123',
            name: 'John Doe',
            email: 'john@example.com',
            token: '1|abc123',
        ));

        $this->artisan('user:logout')
            ->expectsOutputToContain('Logged out successfully')
            ->assertSuccessful();

        expect($session->isAuthenticated())->toBeFalse()
            ->and($this->authClient->logoutCalled)->toBeTrue();
    });

test('logout command displays error on failure',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->authClient->shouldFailLogout();

        $this->artisan('user:logout')
            ->expectsOutputToContain('Unauthenticated.')
            ->assertFailed();
    });
