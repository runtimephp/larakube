<?php

declare(strict_types=1);

namespace App\Client;

use App\Contracts\ServerClient;
use App\Data\CreateServerData;
use App\Data\ServerResourceData;
use App\Data\SyncSummaryData;

final readonly class HttpServerClient implements ServerClient
{
    public function __construct(private LarakubeClient $client) {}

    public function create(CreateServerData $data, string $cloudProviderId): ServerResourceData
    {
        $response = $this->client->post('/api/v1/servers', [
            'name' => $data->name,
            'type' => $data->type,
            'image' => $data->image,
            'region' => $data->region,
            'cloud_provider_id' => $cloudProviderId,
        ]);

        return ServerResourceData::fromArray($response->json('data'));
    }

    /**
     * @return list<ServerResourceData>
     */
    public function list(): array
    {
        $response = $this->client->get('/api/v1/servers');

        return array_map(
            ServerResourceData::fromArray(...),
            $response->json('data'),
        );
    }

    public function show(string $id): ServerResourceData
    {
        $response = $this->client->get("/api/v1/servers/{$id}");

        return ServerResourceData::fromArray($response->json('data'));
    }

    public function delete(string $id): void
    {
        $this->client->delete("/api/v1/servers/{$id}");
    }

    public function sync(string $cloudProviderId): SyncSummaryData
    {
        $response = $this->client->post('/api/v1/servers/sync', [
            'cloud_provider_id' => $cloudProviderId,
        ]);

        return SyncSummaryData::fromArray($response->json('data'));
    }
}
