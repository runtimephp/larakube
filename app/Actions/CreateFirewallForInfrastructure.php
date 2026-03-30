<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Data\CreateFirewallData;
use App\Enums\InfrastructureStatus;
use App\Models\Firewall;
use App\Models\Infrastructure;
use App\Services\CloudProviderFactory;

final readonly class CreateFirewallForInfrastructure implements StepHandler
{
    public function __construct(private CloudProviderFactory $factory) {}

    public function handle(Infrastructure $infrastructure): void
    {
        if ($infrastructure->firewalls()->exists()) {
            return;
        }

        $provider = $infrastructure->cloudProvider;
        $firewallService = $this->factory->makeFirewallService($provider->type, $provider->api_token);

        $firewallData = $firewallService->create(new CreateFirewallData(
            name: "{$infrastructure->name}-firewall",
            infrastructure_id: $infrastructure->id,
        ));

        Firewall::query()->create([
            'infrastructure_id' => $infrastructure->id,
            'name' => $firewallData->name,
            'external_firewall_id' => (string) $firewallData->externalId,
            'status' => InfrastructureStatus::Healthy,
        ]);
    }
}
