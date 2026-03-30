<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\NetworkService;
use App\Data\CreateNetworkData;
use App\Data\NetworkData;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final readonly class HetznerNetworkService implements NetworkService
{
    public function __construct(private string $token) {}

    /**
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function create(CreateNetworkData $data): NetworkData
    {
        $response = Http::withToken($this->token)
            ->post('https://api.hetzner.cloud/v1/networks', [
                'name' => $data->name,
                'ip_range' => $data->cidr,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error.message', 'Failed to create network on Hetzner.'));
        }

        $networkId = $response->json('network.id');

        if ($networkId === null) {
            throw new RuntimeException('Network ID not found in Hetzner response.');
        }

        $subnetResponse = Http::withToken($this->token)
            ->post("https://api.hetzner.cloud/v1/networks/{$networkId}/actions/add_subnet", [
                'type' => 'cloud',
                'network_zone' => 'eu-central',
                'ip_range' => $data->cidr,
            ]);

        if (! $subnetResponse->successful()) {
            throw new RuntimeException($subnetResponse->json('error.message', 'Failed to add subnet to network on Hetzner.'));
        }

        return $this->mapNetworkData($response->json('network'));
    }

    /**
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function list(): Collection
    {
        $response = Http::withToken($this->token)
            ->get('https://api.hetzner.cloud/v1/networks');

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error.message', 'Failed to list networks on Hetzner.'));
        }

        return collect($response->json('networks', []))
            ->map($this->mapNetworkData(...));
    }

    /**
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function find(string $id): ?NetworkData
    {
        $response = Http::withToken($this->token)
            ->get("https://api.hetzner.cloud/v1/networks/{$id}");

        if ($response->status() === 404) {
            return null;
        }

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error.message', 'Failed to find network on Hetzner.'));
        }

        return $this->mapNetworkData($response->json('network'));
    }

    /**
     * @throws ConnectionException
     */
    public function delete(string $id): bool
    {
        $response = Http::withToken($this->token)
            ->delete("https://api.hetzner.cloud/v1/networks/{$id}");

        return $response->successful();
    }

    /**
     * @param  array<string, mixed>  $network
     */
    private function mapNetworkData(array $network): NetworkData
    {
        return new NetworkData(
            externalId: $network['id'],
            name: $network['name'],
            cidr: $network['ip_range'],
        );
    }
}
