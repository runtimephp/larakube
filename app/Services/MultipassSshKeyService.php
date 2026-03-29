<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SshKeyService;
use App\Data\SshKeyData;
use Illuminate\Support\Collection;

final readonly class MultipassSshKeyService implements SshKeyService
{
    public function register(string $name, string $publicKey): SshKeyData
    {
        return new SshKeyData(
            externalId: 'multipass-'.$name,
            name: $name,
            fingerprint: md5($publicKey),
            publicKey: $publicKey,
        );
    }

    public function list(): Collection
    {
        return collect();
    }

    public function delete(int|string $externalId): bool
    {
        return true;
    }
}
