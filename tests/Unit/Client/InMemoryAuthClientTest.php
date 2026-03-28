<?php

declare(strict_types=1);

use App\Client\InMemoryAuthClient;
use App\Data\CreateUserData;
use App\Data\SessionUserData;
use App\Data\UserData;
use App\Exceptions\LarakubeApiException;

beforeEach(function (): void {
    $this->client = new InMemoryAuthClient();
});

test('register returns configured user data',
    /**
     * @throws Throwable
     */
    function (): void {
        $userData = new UserData(id: 'uuid-1', name: 'John', email: 'john@example.com');
        $this->client->setRegisterResponse($userData);

        $result = $this->client->register(new CreateUserData(
            name: 'John',
            email: 'john@example.com',
            password: 'password123',
        ));

        expect($result)->toBe($userData);
    });

test('register throws when configured to fail',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->shouldFailRegister();

        $this->client->register(new CreateUserData(
            name: 'John',
            email: 'john@example.com',
            password: 'password123',
        ));
    })->throws(LarakubeApiException::class);

test('login returns configured session user data',
    /**
     * @throws Throwable
     */
    function (): void {
        $sessionData = new SessionUserData(
            id: 'uuid-1',
            name: 'John',
            email: 'john@example.com',
            token: '1|abc',
        );
        $this->client->setLoginResponse($sessionData);

        $result = $this->client->login('john@example.com', 'password123');

        expect($result)->toBe($sessionData);
    });

test('login throws when configured to fail',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->shouldFailLogin();

        $this->client->login('john@example.com', 'wrong');
    })->throws(LarakubeApiException::class);

test('logout succeeds by default',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->logout();

        expect($this->client->logoutCalled)->toBeTrue();
    });

test('logout throws when configured to fail',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->shouldFailLogout();

        $this->client->logout();
    })->throws(LarakubeApiException::class);
