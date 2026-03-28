<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CreateInfrastructureData;
use App\Data\InfrastructureData;
use App\Exceptions\LarakubeApiException;

interface InfrastructureClient
{
    /**
     * @throws LarakubeApiException
     */
    public function create(CreateInfrastructureData $data, string $cloudProviderId): InfrastructureData;

    /**
     * @return list<InfrastructureData>
     *
     * @throws LarakubeApiException
     */
    public function list(): array;
}
