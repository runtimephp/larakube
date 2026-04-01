<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\SshKeyData;
use Illuminate\Support\Collection;

/**
 * @see ADR-0005, ADR-0009 — Write methods to be removed; refactoring to CloudManager driver pattern
 */
interface SshKeyService
{
    public function register(string $name, string $publicKey): SshKeyData;

    public function list(): Collection;

    public function delete(int|string $externalId): bool;
}
