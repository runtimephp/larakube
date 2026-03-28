<?php

declare(strict_types=1);

namespace App\Client;

use App\Contracts\AuthClient;
use App\Data\CreateUserData;
use App\Data\SessionUserData;
use App\Data\UserData;

final readonly class HttpAuthClient implements AuthClient
{
    public function __construct(private LarakubeClient $client) {}

    public function register(CreateUserData $data): UserData
    {
        $response = $this->client->post('/api/v1/auth/register', [
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
        ]);

        return UserData::fromArray($response->json('data'));
    }

    public function login(string $email, string $password): SessionUserData
    {
        $response = $this->client->post('/api/v1/auth/token', [
            'email' => $email,
            'password' => $password,
        ]);

        return SessionUserData::fromArray($response->json('data'));
    }

    public function logout(): void
    {
        $this->client->delete('/api/v1/auth/token');
    }
}
