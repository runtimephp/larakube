<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CreateNetworkData;
use App\Data\NetworkData;
use Illuminate\Support\Collection;

interface NetworkService
{
    public function create(CreateNetworkData $data): NetworkData;

    public function list(): Collection;

    public function find(string $id): ?NetworkData;

    public function delete(string $id): bool;
}
