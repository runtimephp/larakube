<?php

declare(strict_types=1);

namespace App\Client;

use App\Contracts\CloudProviderClient;
use App\Data\ApiErrorData;
use App\Data\CloudProviderData;
use App\Data\CreateCloudProviderData;
use App\Enums\ApiErrorCode;
use App\Exceptions\LarakubeApiException;

final class InMemoryCloudProviderClient implements CloudProviderClient
{
    public bool $deleteCalled = false;

    public ?string $deletedId = null;

    private ?CloudProviderData $createResponse = null;

    /** @var list<CloudProviderData> */
    private array $listResponse = [];

    private bool $failCreate = false;

    private bool $failList = false;

    private bool $failDelete = false;

    public function setCreateResponse(CloudProviderData $data): void
    {
        $this->createResponse = $data;
    }

    public function shouldFailCreate(): void
    {
        $this->failCreate = true;
    }

    /**
     * @param  list<CloudProviderData>  $data
     */
    public function setListResponse(array $data): void
    {
        $this->listResponse = $data;
    }

    public function shouldFailDelete(): void
    {
        $this->failDelete = true;
    }

    public function create(CreateCloudProviderData $data): CloudProviderData
    {
        if ($this->failCreate) {
            throw new LarakubeApiException(new ApiErrorData(
                message: 'The API token for '.$data->type->label().' is invalid.',
                code: ApiErrorCode::ValidationFailed,
            ));
        }

        return $this->createResponse ?? new CloudProviderData(
            id: 'in-memory-id',
            name: $data->name,
            type: $data->type->value,
            isVerified: true,
        );
    }

    public function shouldFailList(): void
    {
        $this->failList = true;
    }

    /**
     * @return list<CloudProviderData>
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

    public function delete(string $id): void
    {
        if ($this->failDelete) {
            throw new LarakubeApiException(new ApiErrorData(
                message: 'Failed to delete cloud provider.',
                code: ApiErrorCode::NotFound,
            ));
        }

        $this->deleteCalled = true;
        $this->deletedId = $id;
    }
}
