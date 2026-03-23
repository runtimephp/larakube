<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateCloudProviderData;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Services\CloudProviders\CloudProviderClientFactory;
use RuntimeException;

final class CreateCloudProvider
{
    public function __construct(private CloudProviderClientFactory $clientFactory) {}

    /**
     * @throws RuntimeException
     */
    public function handle(CreateCloudProviderData $data, Organization $organization): CloudProvider
    {
        $client = $this->clientFactory->make($data->type);

        if (! $client->validateToken($data->apiToken)) {
            throw new RuntimeException("The API token for {$data->type->label()} is invalid.");
        }

        return $organization->cloudProviders()->create([
            'name' => $data->name,
            'type' => $data->type,
            'api_token' => $data->apiToken,
            'is_verified' => true,
        ]);
    }
}
