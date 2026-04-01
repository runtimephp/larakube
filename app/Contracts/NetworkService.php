<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CreateNetworkData;
use App\Data\NetworkData;
use Illuminate\Support\Collection;

/**
 * @see ADR-0005, ADR-0009 — Write methods to be removed; refactoring to CloudManager driver pattern
 */
interface NetworkService
{
    public function create(CreateNetworkData $data): NetworkData;

    public function list(): Collection;

    public function find(string $id): ?NetworkData;

    public function delete(string $id): bool;
}
