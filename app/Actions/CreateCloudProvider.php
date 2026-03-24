<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\ServiceFactoryInterface;
use App\Data\CreateCloudProviderData;
use App\Models\CloudProvider;
use App\Models\Organization;
use RuntimeException;

final readonly class CreateCloudProvider
{
    public function __construct(private ServiceFactoryInterface $serviceFactory) {}

    /**
     * @throws RuntimeException
     */
    public function handle(CreateCloudProviderData $data, Organization $organization): CloudProvider
    {
        $service = $this->serviceFactory->makeBaseService($data->type);

        if (! $service->validateToken($data->apiToken)) {
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
