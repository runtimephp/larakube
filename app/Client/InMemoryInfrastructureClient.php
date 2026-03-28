<?php

declare(strict_types=1);

namespace App\Client;

use App\Contracts\InfrastructureClient;
use App\Data\ApiErrorData;
use App\Data\CreateInfrastructureData;
use App\Data\InfrastructureData;
use App\Enums\ApiErrorCode;
use App\Exceptions\LarakubeApiException;

final class InMemoryInfrastructureClient implements InfrastructureClient
{
    private ?InfrastructureData $createResponse = null;

    /** @var list<InfrastructureData> */
    private array $listResponse = [];

    private bool $failCreate = false;

    private bool $failList = false;

    public function setCreateResponse(InfrastructureData $data): void
    {
        $this->createResponse = $data;
    }

    public function shouldFailCreate(): void
    {
        $this->failCreate = true;
    }

    /**
     * @param  list<InfrastructureData>  $data
     */
    public function setListResponse(array $data): void
    {
        $this->listResponse = $data;
    }

    public function shouldFailList(): void
    {
        $this->failList = true;
    }

    public function create(CreateInfrastructureData $data, string $cloudProviderId): InfrastructureData
    {
        if ($this->failCreate) {
            throw new LarakubeApiException(new ApiErrorData(
                message: 'Validation failed.',
                code: ApiErrorCode::ValidationFailed,
            ));
        }

        return $this->createResponse ?? new InfrastructureData(
            id: 'in-memory-id',
            name: $data->name,
            description: $data->description,
            status: 'healthy',
            cloudProviderId: $cloudProviderId,
        );
    }

    /**
     * @return list<InfrastructureData>
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
