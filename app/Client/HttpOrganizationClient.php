<?php

declare(strict_types=1);

namespace App\Client;

use App\Contracts\OrganizationClient;
use App\Data\CreateOrganizationData;
use App\Data\OrganizationData;

final readonly class HttpOrganizationClient implements OrganizationClient
{
    public function __construct(private LarakubeClient $client) {}

    public function create(CreateOrganizationData $data): OrganizationData
    {
        $response = $this->client->post('/api/v1/organizations', [
            'name' => $data->name,
            'description' => $data->description,
        ]);

        return OrganizationData::fromArray($response->json('data'));
    }

    /**
     * @return list<OrganizationData>
     */
    public function list(): array
    {
        $response = $this->client->get('/api/v1/organizations');

        return array_map(
            OrganizationData::fromArray(...),
            $response->json('data'),
        );
    }
}
