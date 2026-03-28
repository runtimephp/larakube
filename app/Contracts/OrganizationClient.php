<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CreateOrganizationData;
use App\Data\OrganizationData;
use App\Exceptions\LarakubeApiException;

interface OrganizationClient
{
    /**
     * @throws LarakubeApiException
     */
    public function create(CreateOrganizationData $data): OrganizationData;

    /**
     * @return list<OrganizationData>
     *
     * @throws LarakubeApiException
     */
    public function list(): array;
}
