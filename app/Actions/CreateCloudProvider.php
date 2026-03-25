<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateCloudProviderData;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Services\CloudProviderFactory;
use RuntimeException;

final readonly class CreateCloudProvider
{
    public function __construct(private CloudProviderFactory $factory) {}

    /**
     * @throws RuntimeException
     */
    public function handle(CreateCloudProviderData $data, Organization $organization): CloudProvider
    {
        $validationService = $this->factory->makeForValidation($data->type, $data->apiToken);

        if (! $validationService->validateToken()) {
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
