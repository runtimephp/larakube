<?php

declare(strict_types=1);

namespace App\Client;

use App\Contracts\OrganizationClient;
use App\Data\ApiErrorData;
use App\Data\CreateOrganizationData;
use App\Data\OrganizationData;
use App\Enums\ApiErrorCode;
use App\Exceptions\LarakubeApiException;

final class InMemoryOrganizationClient implements OrganizationClient
{
    private ?OrganizationData $createResponse = null;

    /** @var list<OrganizationData> */
    private array $listResponse = [];

    private bool $failCreate = false;

    private bool $failList = false;

    public function setCreateResponse(OrganizationData $data): void
    {
        $this->createResponse = $data;
    }

    public function shouldFailCreate(): void
    {
        $this->failCreate = true;
    }

    /**
     * @param  list<OrganizationData>  $data
     */
    public function setListResponse(array $data): void
    {
        $this->listResponse = $data;
    }

    public function create(CreateOrganizationData $data): OrganizationData
    {
        if ($this->failCreate) {
            throw new LarakubeApiException(new ApiErrorData(
                message: 'Validation failed.',
                code: ApiErrorCode::ValidationFailed,
            ));
        }

        return $this->createResponse ?? new OrganizationData(
            id: 'in-memory-id',
            name: $data->name,
            slug: str($data->name)->slug()->toString(),
            description: $data->description,
        );
    }

    public function shouldFailList(): void
    {
        $this->failList = true;
    }

    /**
     * @return list<OrganizationData>
     */
    public function list(): array
    {
        if ($this->failList) {
            throw new LarakubeApiException(new ApiErrorData(
                message: 'Unauthenticated.',
                code: ApiErrorCode::Unauthenticated,
            ));
        }

        return $this->listResponse;
    }
}
