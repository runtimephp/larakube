<?php

declare(strict_types=1);

namespace App\Services\Hetzner;

use App\Contracts\ServerServiceContract;
use App\Data\CreateServerData;
use App\Data\ServerData;
use App\Enums\ServerStatus;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use SensitiveParameter;

class HetznerServerService implements ServerServiceContract
{
    private const string BASE_URL = 'https://api.hetzner.cloud/v1';

    /**
     * @return array<int, ServerData>
     *
     * @throws ConnectionException
     */
    public function getServers(#[SensitiveParameter] string $token): array
    {
        $response = Http::withToken($token)
            ->get(self::BASE_URL.'/servers');

        return array_map(
            $this->mapServerData(...),
            $response->json('servers', []),
        );
    }

    /**
     * @throws ConnectionException
     */
    public function createServer(#[SensitiveParameter] string $token, CreateServerData $data): ServerData
    {
        $response = Http::withToken($token)
            ->post(self::BASE_URL.'/servers', [
                'name' => $data->name,
                'server_type' => $data->type,
                'image' => $data->image,
                'location' => $data->region,
            ]);

        return $this->mapServerData($response->json('server'));
    }

    /**
     * @throws ConnectionException
     */
    public function getServerByName(#[SensitiveParameter] string $token, string $name): ?ServerData
    {
        $response = Http::withToken($token)
            ->get(self::BASE_URL.'/servers', ['name' => $name]);

        $servers = $response->json('servers', []);

        if ($servers === []) {
            return null;
        }

        return $this->mapServerData($servers[0]);
    }

    /**
     * @throws ConnectionException
     */
    public function deleteServer(#[SensitiveParameter] string $token, int|string $externalId): bool
    {
        $response = Http::withToken($token)
            ->delete(self::BASE_URL."/servers/{$externalId}");

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
