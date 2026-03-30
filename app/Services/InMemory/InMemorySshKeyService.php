<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\SshKeyService;
use App\Data\SshKeyData;
use Illuminate\Support\Collection;
use RuntimeException;

final class InMemorySshKeyService implements SshKeyService
{
    /** @var Collection<int, SshKeyData> */
    private Collection $keys;

    private bool $failRegister = false;

    private bool $failDelete = false;

    private bool $throwOnDelete = false;

    private int $nextId = 1;

    public function __construct()
    {
        $this->keys = collect();
    }

    public function addKey(SshKeyData $key): self
    {
        $this->keys->push($key);

        $externalId = (string) $key->externalId;

        if (ctype_digit($externalId) && (int) $externalId >= $this->nextId) {
            $this->nextId = (int) $externalId + 1;
        }

        return $this;
    }

    public function shouldFailRegister(bool $fail = true): self
    {
        $this->failRegister = $fail;

        return $this;
    }

    public function shouldFailDelete(bool $fail = true): self
    {
        $this->failDelete = $fail;

        return $this;
    }

    public function shouldThrowOnDelete(bool $throw = true): self
    {
        $this->throwOnDelete = $throw;

        return $this;
    }

    public function register(string $name, string $publicKey): SshKeyData
    {
        if ($this->failRegister) {
            throw new RuntimeException('Simulated API failure on register');
        }

        $key = new SshKeyData(
            externalId: (string) $this->nextId++,
            name: $name,
            fingerprint: md5($publicKey),
            publicKey: $publicKey,
        );

        $this->keys->push($key);

        return $key;
    }

    public function list(): Collection
    {
        return $this->keys->values();
    }

    public function delete(int|string $externalId): bool
    {
        if ($this->throwOnDelete) {
            throw new RuntimeException('Simulated API failure on delete');
        }

        if ($this->failDelete) {
            return false;
        }

        $before = $this->keys->count();
        $this->keys = $this->keys->reject(
            fn (SshKeyData $key): bool => (string) $key->externalId === (string) $externalId
        )->values();

        return $this->keys->count() < $before;
    }
}
