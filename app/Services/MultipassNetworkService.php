<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\NetworkService;
use App\Data\CreateNetworkData;
use App\Data\NetworkData;
use Illuminate\Support\Collection;

final readonly class MultipassNetworkService implements NetworkService
{
    public function create(CreateNetworkData $data): NetworkData
    {
        return new NetworkData(
            externalId: 'multipass-'.$data->name,
            name: $data->name,
            cidr: $data->cidr,
        );
    }

    public function list(): Collection
    {
        return collect();
    }

    public function find(string $id): ?NetworkData
    {
        return null;
    }

    public function delete(string $id): bool
    {
        return true;
    }
}
