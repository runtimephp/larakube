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

test('login command authenticates user successfully',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->authClient->setLoginResponse(new SessionUserData(
            id: 'uuid-123',
            name: 'John Doe',
            email: 'john@example.com',
            token: '1|abc123',
        ));

        $this->artisan('user:login')
            ->expectsQuestion('Email', 'john@example.com')
            ->expectsQuestion('Password', 'password123')
            ->expectsOutputToContain('Logged in as [John Doe]')
            ->assertSuccessful();

        $session = app(SessionManager::class);
        expect($session->isAuthenticated())->toBeTrue()
            ->and($session->getUser())->not->toBeNull()
            ->and($session->getUser()->name)->toBe('John Doe');
    });

test('login command fails with invalid credentials',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->authClient->shouldFailLogin();

        $this->artisan('user:login')
            ->expectsQuestion('Email', 'john@example.com')
            ->expectsQuestion('Password', 'wrong-password')
            ->expectsOutputToContain('Invalid credentials.')
            ->assertFailed();
    });
