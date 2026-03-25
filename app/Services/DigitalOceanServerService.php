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

final readonly class DigitalOceanServerService implements ServerService
{
    public function __construct(private string $token) {}

    /**
     * @throws ConnectionException
     */
    public function getAll(): Collection
    {
        $response = Http::withToken($this->token)
            ->get('https://api.digitalocean.com/v2/droplets');

        return collect($response->json('droplets', []))
            ->map($this->mapServerData(...));
    }

    /**
     * @throws ConnectionException
     */
    public function create(CreateServerData $data): ServerData
    {
        $response = Http::withToken($this->token)
            ->post('https://api.digitalocean.com/v2/droplets', [
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
    public function find(string $name): ?ServerData
    {
        $response = Http::withToken($this->token)
            ->get('https://api.digitalocean.com/v2/droplets', ['name' => $name]);

        $droplets = $response->json('droplets', []);

        if ($droplets === []) {
            return null;
        }

        return $this->mapServerData($droplets[0]);
    }

    /**
     * @throws ConnectionException
     */
    public function destroy(int|string $externalId): bool
    {
        $response = Http::withToken($this->token)
            ->delete("https://api.digitalocean.com/v2/droplets/{$externalId}");

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
