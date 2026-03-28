<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CreateServerData;
use App\Data\ServerResourceData;
use App\Exceptions\LarakubeApiException;

interface ServerClient
{
    /**
     * @throws LarakubeApiException
     */
    public function create(CreateServerData $data, string $cloudProviderId): ServerResourceData;

    /**
     * @return list<ServerResourceData>
     *
     * @throws LarakubeApiException
     */
    public function list(): array;

    /**
     * @throws LarakubeApiException
     */
    public function show(string $id): ServerResourceData;

    /**
     * @throws LarakubeApiException
     */
    public function delete(string $id): void;
}
