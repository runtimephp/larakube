<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CreateServerData;
use App\Data\ServerData;
use App\Models\CloudProvider;

interface ServerManagerInterface
{
    /** @return array<int, ServerData> */
    public function list(CloudProvider $provider): array;

    public function create(CloudProvider $provider, CreateServerData $data): ServerData;

    public function findByName(CloudProvider $provider, string $name): ?ServerData;

    public function delete(CloudProvider $provider, int|string $externalId): bool;
}
