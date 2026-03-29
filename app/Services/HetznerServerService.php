<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServerService;
use App\Data\CreateServerData;
use App\Data\ServerData;
use App\Enums\ServerStatus;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final readonly class HetznerServerService implements ServerService
{
    public function __construct(private string $token) {}

    /**
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function getAll(): Collection
    {
        $response = Http::withToken($this->token)
            ->get('https://api.hetzner.cloud/v1/servers');

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error.message', 'Failed to fetch servers from Hetzner.'));
        }

        return collect($response->json('servers', []))
            ->map($this->mapServerData(...));
    }

    /**
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function create(CreateServerData $data): ServerData
    {
        $payload = [
            'name' => $data->name,
            'server_type' => $data->type,
            'image' => $data->image,
            'location' => $data->region,
        ];

        if ($data->sshKeyIds !== []) {
            $payload['ssh_keys'] = $data->sshKeyIds;
        }

        if ($data->cloudInit !== null) {
            $payload['user_data'] = $data->cloudInit;
        }

        $response = Http::withToken($this->token)
            ->post('https://api.hetzner.cloud/v1/servers', $payload);

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error.message', 'Failed to create server on Hetzner.'));
        }

        return $this->mapServerData($response->json('server'));
    }

    /**
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function find(string $name): ?ServerData
    {
        $response = Http::withToken($this->token)
            ->get('https://api.hetzner.cloud/v1/servers', ['name' => $name]);

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error.message', 'Failed to search servers on Hetzner.'));
        }

        $servers = $response->json('servers', []);

        if ($servers === []) {
            return null;
        }

        return $this->mapServerData($servers[0]);
    }

    /**
     * @throws ConnectionException
     */
    public function destroy(int|string $externalId): bool
    {
        $response = Http::withToken($this->token)
            ->delete("https://api.hetzner.cloud/v1/servers/{$externalId}");

        return $response->successful();
    }

    /**
     * @param  array<string, mixed>  $server
     */
    private function mapServerData(array $server): ServerData
    {
        return new ServerData(
            externalId: $server['id'],
            name: $server['name'],
            status: ServerStatus::fromHetzner($server['status']),
            type: $server['server_type']['name'] ?? '',
            region: $server['datacenter']['name'] ?? '',
            ipv4: $server['public_net']['ipv4']['ip'] ?? null,
            ipv6: $server['public_net']['ipv6']['ip'] ?? null,
        );
    }
}
