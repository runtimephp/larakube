<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\SshKeyData;
use Illuminate\Support\Collection;

interface SshKeyService
{
    public function register(string $name, string $publicKey): SshKeyData;

    public function list(): Collection;

    public function delete(int|string $externalId): bool;
}
