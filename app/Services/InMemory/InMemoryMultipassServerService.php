<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\ServerService;
use App\Data\CreateServerData;
use App\Data\ServerData;
use App\Enums\ServerStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

final class InMemoryMultipassServerService implements ServerService
{
    /** @var Collection<int, ServerData> */
    private readonly Collection $servers;

    /** @var array<string, bool> */
    private array $deletedServers = [];

    private bool $shouldFailCreate = false;

    private bool $shouldFailDelete = false;

    public function __construct()
    {
        $this->servers = collect([]);
    }

    public function addServer(ServerData $server): self
    {
        $this->servers->push($server);

        return $this;
    }

    public function shouldFailCreate(bool $fail = true): self
    {
        $this->shouldFailCreate = $fail;

        return $this;
    }

    public function shouldFailDelete(bool $fail = true): self
    {
        $this->shouldFailDelete = $fail;

        return $this;
    }

    public function getAll(): Collection
    {
        return $this->servers->filter(
            fn (ServerData $server): bool => ! isset($this->deletedServers[(string) $server->externalId])
        );
    }

    public function create(CreateServerData $data): ServerData
    {
        if ($this->shouldFailCreate) {
            throw new RuntimeException('Failed to create Multipass VM.');
        }

        $name = $data->name.'-'.Str::random(6);

        $serverData = new ServerData(
            externalId: $name,
            name: $name,
            status: ServerStatus::Running,
            type: 'custom',
            region: 'local',
            ipv4: '192.168.64.'.random_int(2, 254),
        );

        $this->servers->push($serverData);

        return $serverData;
    }

    public function find(string $name): ?ServerData
    {
        return $this->servers->first(
            fn (ServerData $server): bool => $server->name === $name
                && ! isset($this->deletedServers[(string) $server->externalId])
        );
    }

    public function destroy(int|string $externalId): bool
    {
        if ($this->shouldFailDelete) {
            return false;
        }

        $server = $this->servers->first(
            fn (ServerData $server): bool => (string) $server->externalId === (string) $externalId
        );

        if ($server === null) {
            return false;
        }

        $this->deletedServers[(string) $externalId] = true;

        return true;
    }
}
