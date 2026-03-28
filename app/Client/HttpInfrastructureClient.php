<?php

declare(strict_types=1);

namespace App\Client;

use App\Contracts\InfrastructureClient;
use App\Data\CreateInfrastructureData;
use App\Data\InfrastructureData;

final readonly class HttpInfrastructureClient implements InfrastructureClient
{
    public function __construct(private LarakubeClient $client) {}

    public function create(CreateInfrastructureData $data, string $cloudProviderId): InfrastructureData
    {
        $response = $this->client->post('/api/v1/infrastructures', [
            'name' => $data->name,
            'description' => $data->description,
            'cloud_provider_id' => $cloudProviderId,
        ]);

        return InfrastructureData::fromArray($response->json('data'));
    }

    /**
     * @return list<InfrastructureData>
     */
    public function list(): array
    {
        $response = $this->client->get('/api/v1/infrastructures');

        return array_map(
            InfrastructureData::fromArray(...),
            $response->json('data'),
        );
    }
}
