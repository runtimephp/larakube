<?php

declare(strict_types=1);

namespace App\Client;

use App\Contracts\CloudProviderClient;
use App\Data\CloudProviderData;
use App\Data\CreateCloudProviderData;

final readonly class HttpCloudProviderClient implements CloudProviderClient
{
    public function __construct(private LarakubeClient $client) {}

    public function create(CreateCloudProviderData $data): CloudProviderData
    {
        $response = $this->client->post('/api/v1/cloud-providers', [
            'name' => $data->name,
            'type' => $data->type->value,
            'api_token' => $data->apiToken,
        ]);

        return CloudProviderData::fromArray($response->json('data'));
    }

    /**
     * @return list<CloudProviderData>
     */
    public function list(): array
    {
        $response = $this->client->get('/api/v1/cloud-providers');

        return array_map(
            CloudProviderData::fromArray(...),
            $response->json('data'),
        );
    }

    public function delete(string $id): void
    {
        $this->client->delete("/api/v1/cloud-providers/{$id}");
    }
}
