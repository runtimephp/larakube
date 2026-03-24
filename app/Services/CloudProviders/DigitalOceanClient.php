<?php

declare(strict_types=1);

namespace App\Services\CloudProviders;

use App\Contracts\CloudProviderClient;
use App\Data\CreateServerData;
use App\Data\ServerData;
use App\Enums\ServerStatus;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use SensitiveParameter;

final class DigitalOceanClient implements CloudProviderClient
{
    private const string BASE_URL = 'https://api.digitalocean.com/v2';

    /**
     * @throws ConnectionException
     */
    public function validateToken(#[SensitiveParameter] string $token): bool
    {
        $response = Http::withToken($token)
            ->get(self::BASE_URL.'/account');

        return $response->successful();
    }

    /**
     * @return array<int, ServerData>
     *
     * @throws ConnectionException
     */
    public function getServers(#[SensitiveParameter] string $token): array
    {
        $response = Http::withToken($token)
            ->get(self::BASE_URL.'/droplets');

        return array_map(
            $this->mapServerData(...),
            $response->json('droplets', []),
        );
    }

    /**
     * @throws ConnectionException
     */
    public function createServer(#[SensitiveParameter] string $token, CreateServerData $data): ServerData
    {
        $response = Http::withToken($token)
            ->post(self::BASE_URL.'/droplets', [
                'name' => $data->name,
                'size' => $data->type,
                'image' => $data->image,
                'region' => $data->region,
            ]);

        return $this->mapServerData($response->json('droplet'));
    }

    /**
     * @throws ConnectionException
     */
    public function getServerByName(#[SensitiveParameter] string $token, string $name): ?ServerData
    {
        $response = Http::withToken($token)
            ->get(self::BASE_URL.'/droplets', ['name' => $name]);

        $droplets = $response->json('droplets', []);

        if ($droplets === []) {
            return null;
        }

        return $this->mapServerData($droplets[0]);
    }

    /**
     * @throws ConnectionException
     */
    public function deleteServer(#[SensitiveParameter] string $token, int|string $externalId): bool
    {
        $response = Http::withToken($token)
            ->delete(self::BASE_URL."/droplets/{$externalId}");

        return $response->successful();
    }

    /**
     * @param  array<string, mixed>  $droplet
     */
    private function mapServerData(array $droplet): ServerData
    {
        $ipv4 = null;
        foreach ($droplet['networks']['v4'] ?? [] as $network) {
            if ($network['type'] === 'public') {
                $ipv4 = $network['ip_address'];
                break;
            }
        }

        return new ServerData(
            externalId: $droplet['id'],
            name: $droplet['name'],
            status: ServerStatus::fromDigitalOcean($droplet['status']),
            type: $droplet['size']['slug'] ?? '',
            region: $droplet['region']['slug'] ?? '',
            ipv4: $ipv4,
            ipv6: null,
        );
    }
}
