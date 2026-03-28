<?php

declare(strict_types=1);

namespace App\Client;

use App\Contracts\AuthClient;
use App\Data\ApiErrorData;
use App\Data\CreateUserData;
use App\Data\SessionUserData;
use App\Data\UserData;
use App\Enums\ApiErrorCode;
use App\Exceptions\LarakubeApiException;

final class InMemoryAuthClient implements AuthClient
{
    public bool $logoutCalled = false;

    private ?UserData $registerResponse = null;

    private ?SessionUserData $loginResponse = null;

    private bool $failRegister = false;

    private bool $failLogin = false;

    private bool $failLogout = false;

    public function setRegisterResponse(UserData $data): void
    {
        $this->registerResponse = $data;
    }

    public function shouldFailRegister(): void
    {
        $this->failRegister = true;
    }

    public function setLoginResponse(SessionUserData $data): void
    {
        $this->loginResponse = $data;
    }

    public function shouldFailLogin(): void
    {
        $this->failLogin = true;
    }

    public function shouldFailLogout(): void
    {
        $this->failLogout = true;
    }

    public function register(CreateUserData $data): UserData
    {
        if ($this->failRegister) {
            throw new LarakubeApiException(new ApiErrorData(
                message: 'Validation failed.',
                code: ApiErrorCode::ValidationFailed,
            ));
        }

        return $this->registerResponse ?? new UserData(
            id: 'in-memory-id',
            name: $data->name,
            email: $data->email,
        );
    }

    public function login(string $email, string $password): SessionUserData
    {
        if ($this->failLogin) {
            throw new LarakubeApiException(new ApiErrorData(
                message: 'Invalid credentials.',
                code: ApiErrorCode::InvalidCredentials,
            ));
        }

        return $this->loginResponse ?? new SessionUserData(
            id: 'in-memory-id',
            name: 'In Memory User',
            email: $email,
            token: 'in-memory-token',
        );
    }

    public function logout(): void
    {
        if ($this->failLogout) {
            throw new LarakubeApiException(new ApiErrorData(
                message: 'Unauthenticated.',
                code: ApiErrorCode::Unauthenticated,
            ));
        }

        $this->logoutCalled = true;
    }
}
