<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CreateServerData;
use App\Data\ServerData;
use SensitiveParameter;

interface ServerServiceContract
{
    /**
     * @return array<int, ServerData>
     */
    public function getServers(#[SensitiveParameter] string $token): array;

    public function createServer(#[SensitiveParameter] string $token, CreateServerData $data): ServerData;

    public function getServerByName(#[SensitiveParameter] string $token, string $name): ?ServerData;

    public function deleteServer(#[SensitiveParameter] string $token, int|string $externalId): bool;
}
