<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CreateServerData;
use App\Data\ServerData;
use Illuminate\Support\Collection;

/**
 * @see ADR-0005, ADR-0009 — Write methods to be removed; refactoring to CloudManager driver pattern
 */
interface ServerService
{
    public function getAll(): Collection;

    public function create(CreateServerData $data): ServerData;

    public function destroy(int|string $externalId): bool;

    public function find(string $name): ?ServerData;
}
