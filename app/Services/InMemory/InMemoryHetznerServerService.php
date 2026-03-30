<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\ServerService;
use App\Data\CreateServerData;
use App\Data\ServerData;
use App\Enums\ServerStatus;
use Illuminate\Support\Collection;

/**
 * In-memory implementation of ServerService for testing.
 *
 * Allows tests to control server operations without making real API calls.
 * Maintains an internal state of servers that can be manipulated for testing.
 */
final class InMemoryHetznerServerService implements ServerService
{
    /**
     * @var Collection<int, ServerData>
     */
    private Collection $servers;

    /**
     * @var array<int, bool> Map of server ID to deletion status
     */
    private array $deletedServers = [];

    private bool $shouldFailCreate = false;

    private bool $shouldFailDelete = false;

    private bool $shouldThrowOnDelete = false;

    public function __construct()
    {
        $this->servers = collect([]);
    }

    /**
     * Set the initial state of servers in the in-memory store.
     *
     * @param  array<int, ServerData>  $servers
     */
    public function setServers(array $servers): self
    {
        $this->servers = collect($servers);

        return $this;
    }

    /**
     * Add a server to the in-memory store.
     */
    public function addServer(ServerData $server): self
    {
        $this->servers->push($server);

        return $this;
    }

    /**
     * Set whether create operations should fail.
     */
    public function shouldFailCreate(bool $fail = true): self
    {
        $this->shouldFailCreate = $fail;

        return $this;
    }

    /**
     * Set whether delete operations should fail.
     */
    public function shouldFailDelete(bool $fail = true): self
    {
        $this->shouldFailDelete = $fail;

        return $this;
    }

    /**
     * Set whether delete operations should throw an exception.
     */
    public function shouldThrowOnDelete(bool $throw = true): self
    {
        $this->shouldThrowOnDelete = $throw;

        return $this;
    }

    public function getAll(): Collection
    {
        return $this->servers->filter(
            fn (ServerData $server) => ! isset($this->deletedServers[$server->externalId])
        );
    }

    public function create(CreateServerData $data): ServerData
    {
        if ($this->shouldFailCreate) {
            throw new \RuntimeException('Simulated API failure on create');
        }

        $serverData = new ServerData(
            externalId: random_int(1000, 9999),
            name: $data->name,
            status: ServerStatus::Running,
            type: $data->type,
            region: $data->region,
            ipv4: '192.168.1.' . random_int(1, 254),
            ipv6: null,
        );

        $this->servers->push($serverData);

        return $serverData;
    }

    public function find(string $name): ?ServerData
    {
        return $this->servers->first(
            fn (ServerData $server) => $server->name === $name
                && ! isset($this->deletedServers[$server->externalId])
        );
    }

    public function destroy(int|string $externalId): bool
    {
        if ($this->shouldThrowOnDelete) {
            throw new \RuntimeException('Simulated API failure on destroy');
        }

        if ($this->shouldFailDelete) {
            return false;
        }

        $server = $this->servers->first(
            fn (ServerData $server) => (string) $server->externalId === (string) $externalId
        );

        if ($server === null) {
            return false;
        }

        $this->deletedServers[$externalId] = true;

        return true;
    }
}
