<?php

declare(strict_types=1);

use App\Client\InMemoryAuthClient;
use App\Contracts\AuthClient;
use App\Data\UserData;

beforeEach(function (): void {
    $this->authClient = new InMemoryAuthClient();
    $this->app->instance(AuthClient::class, $this->authClient);
});

test('register user command creates a user',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->authClient->setRegisterResponse(new UserData(
            id: '550e8400-e29b-41d4-a716-446655440000',
            name: 'John Doe',
            email: 'john@example.com',
        ));

        $this->artisan('user:register')
            ->expectsQuestion('Name', 'John Doe')
            ->expectsQuestion('Email', 'john@example.com')
            ->expectsQuestion('Password', 'password123')
            ->expectsOutputToContain('User [John Doe] registered successfully')
            ->assertSuccessful();
    });

test('register user command displays error on failure',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->authClient->shouldFailRegister();

        $this->artisan('user:register')
            ->expectsQuestion('Name', 'John Doe')
            ->expectsQuestion('Email', 'john@example.com')
            ->expectsQuestion('Password', 'password123')
            ->expectsOutputToContain('Validation failed.')
            ->assertFailed();
    });
