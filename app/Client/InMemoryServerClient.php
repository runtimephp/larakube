<?php

declare(strict_types=1);

namespace App\Client;

use App\Contracts\ServerClient;
use App\Data\ApiErrorData;
use App\Data\CreateServerData;
use App\Data\ServerResourceData;
use App\Data\SyncSummaryData;
use App\Enums\ApiErrorCode;
use App\Exceptions\LarakubeApiException;

final class InMemoryServerClient implements ServerClient
{
    public bool $deleteCalled = false;

    public ?string $deletedId = null;

    private ?ServerResourceData $createResponse = null;

    /** @var list<ServerResourceData> */
    private array $listResponse = [];

    private ?ServerResourceData $showResponse = null;

    private bool $failCreate = false;

    private bool $failList = false;

    private bool $failShow = false;

    private bool $failDelete = false;

    private ?SyncSummaryData $syncResponse = null;

    private bool $failSync = false;

    public function setCreateResponse(ServerResourceData $data): void
    {
        $this->createResponse = $data;
    }

    public function shouldFailCreate(): void
    {
        $this->failCreate = true;
    }

    /**
     * @param  list<ServerResourceData>  $data
     */
    public function setListResponse(array $data): void
    {
        $this->listResponse = $data;
    }

    public function shouldFailList(): void
    {
        $this->failList = true;
    }

    public function setShowResponse(ServerResourceData $data): void
    {
        $this->showResponse = $data;
    }

    public function shouldFailShow(): void
    {
        $this->failShow = true;
    }

    public function shouldFailDelete(): void
    {
        $this->failDelete = true;
    }

    public function create(CreateServerData $data, string $cloudProviderId): ServerResourceData
    {
        if ($this->failCreate) {
            throw new LarakubeApiException(new ApiErrorData(
                message: 'Failed to create server.',
                code: ApiErrorCode::ValidationFailed,
            ));
        }

        return $this->createResponse ?? new ServerResourceData(
            id: 'in-memory-id',
            name: $data->name,
            status: 'running',
            type: $data->type,
            region: $data->region,
            ipv4: null,
            ipv6: null,
            externalId: 'ext-123',
            cloudProviderId: $cloudProviderId,
            infrastructureId: $data->infrastructure_id,
        );
    }

    /**
     * @return list<ServerResourceData>
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

    public function show(string $id): ServerResourceData
    {
        if ($this->failShow) {
            throw new LarakubeApiException(new ApiErrorData(
                message: 'Server not found.',
                code: ApiErrorCode::NotFound,
            ));
        }

        return $this->showResponse ?? new ServerResourceData(
            id: $id,
            name: 'in-memory-server',
            status: 'running',
            type: 'cx11',
            region: 'fsn1',
            ipv4: null,
            ipv6: null,
            externalId: 'ext-123',
            cloudProviderId: 'cp-1',
            infrastructureId: null,
        );
    }

    public function delete(string $id): void
    {
        if ($this->failDelete) {
            throw new LarakubeApiException(new ApiErrorData(
                message: 'Failed to delete server.',
                code: ApiErrorCode::ValidationFailed,
            ));
        }

        $this->deleteCalled = true;
        $this->deletedId = $id;
    }

    public function setSyncResponse(SyncSummaryData $data): void
    {
        $this->syncResponse = $data;
    }

    public function shouldFailSync(): void
    {
        $this->failSync = true;
    }

    public function sync(string $cloudProviderId): SyncSummaryData
    {
        if ($this->failSync) {
            throw new LarakubeApiException(new ApiErrorData(
                message: 'Failed to sync servers.',
                code: ApiErrorCode::ValidationFailed,
            ));
        }

        return $this->syncResponse ?? new SyncSummaryData(created: 0, updated: 0, deleted: 0);
    }
}
