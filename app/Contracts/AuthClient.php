<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CreateUserData;
use App\Data\SessionUserData;
use App\Data\UserData;
use App\Exceptions\LarakubeApiException;

interface AuthClient
{
    /**
     * @throws LarakubeApiException
     */
    public function register(CreateUserData $data): UserData;

    /**
     * @throws LarakubeApiException
     */
    public function login(string $email, string $password): SessionUserData;

    /**
     * @throws LarakubeApiException
     */
    public function logout(): void;
}
