<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Data\CreateNetworkData;
use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;
use App\Models\Network;
use App\Services\CloudProviderFactory;

final readonly class CreateNetworkForInfrastructure implements StepHandler
{
    public function __construct(private CloudProviderFactory $factory) {}

    public function handle(Infrastructure $infrastructure): void
    {
        if ($infrastructure->networks()->exists()) {
            return;
        }

        $provider = $infrastructure->cloudProvider;
        $networkService = $this->factory->makeNetworkService($provider->type, $provider->api_token);

        $networkData = $networkService->create(new CreateNetworkData(
            name: "{$infrastructure->name}-vpc",
            cidr: '10.0.0.0/16',
            infrastructure_id: $infrastructure->id,
        ));

        Network::query()->create([
            'infrastructure_id' => $infrastructure->id,
            'name' => $networkData->name,
            'external_network_id' => (string) $networkData->externalId,
            'cidr' => $networkData->cidr,
            'status' => InfrastructureStatus::Healthy,
        ]);
    }
}
