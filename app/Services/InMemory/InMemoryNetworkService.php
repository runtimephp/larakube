<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\NetworkService;
use App\Data\CreateNetworkData;
use App\Data\NetworkData;
use Illuminate\Support\Collection;
use RuntimeException;

final class InMemoryNetworkService implements NetworkService
{
    /** @var Collection<int, NetworkData> */
    private Collection $networks;

    private bool $failCreate = false;

    private bool $failDelete = false;

    private int $nextId = 1;

    public function __construct()
    {
        $this->networks = collect();
    }

    public function addNetwork(NetworkData $network): self
    {
        $this->networks->push($network);

        $externalId = (string) $network->externalId;

        if (ctype_digit($externalId) && (int) $externalId >= $this->nextId) {
            $this->nextId = (int) $externalId + 1;
        }

        return $this;
    }

    public function shouldFailCreate(bool $fail = true): self
    {
        $this->failCreate = $fail;

        return $this;
    }

    public function shouldFailDelete(bool $fail = true): self
    {
        $this->failDelete = $fail;

        return $this;
    }

    public function create(CreateNetworkData $data): NetworkData
    {
        if ($this->failCreate) {
            throw new RuntimeException('Simulated API failure on create');
        }

        $network = new NetworkData(
            externalId: (string) $this->nextId++,
            name: $data->name,
            cidr: $data->cidr,
        );

        $this->networks->push($network);

        return $network;
    }

    public function list(): Collection
    {
        return $this->networks->values();
    }

    public function find(string $id): ?NetworkData
    {
        return $this->networks->first(
            fn (NetworkData $network): bool => (string) $network->externalId === $id
        );
    }

    public function delete(string $id): bool
    {
        if ($this->failDelete) {
            return false;
        }

        $before = $this->networks->count();
        $this->networks = $this->networks->reject(
            fn (NetworkData $network): bool => (string) $network->externalId === $id
        )->values();

        return $this->networks->count() < $before;
    }
}
