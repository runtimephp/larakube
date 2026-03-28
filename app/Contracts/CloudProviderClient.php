<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CloudProviderData;
use App\Data\CreateCloudProviderData;
use App\Exceptions\LarakubeApiException;

interface CloudProviderClient
{
    /**
     * @throws LarakubeApiException
     */
    public function create(CreateCloudProviderData $data): CloudProviderData;

    /**
     * @return list<CloudProviderData>
     *
     * @throws LarakubeApiException
     */
    public function list(): array;

    /**
     * @throws LarakubeApiException
     */
    public function delete(string $id): void;
}
