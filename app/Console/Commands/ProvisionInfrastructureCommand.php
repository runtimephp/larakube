<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\InfrastructureStatus;
use App\Enums\ProvisioningPhase;
use App\Enums\ProvisioningStep;
use App\Jobs\ProcessProvisioningStep;
use App\Models\Infrastructure;
use App\Queries\InfrastructureQuery;

final class ProvisionInfrastructureCommand extends AuthenticatedCommand
{
    protected $signature = 'infrastructure:provision';

    protected $description = 'Start provisioning a Kubernetes cluster for the selected infrastructure';

    protected bool $requiresOrganization = true;

    protected bool $requiresInfrastructure = true;

    public function handleCommand(InfrastructureQuery $query): int
    {
        $infrastructure = ($query)()
            ->byId($this->infrastructure->id)
            ->byOrganizationId($this->organization->id)
            ->first();

        if (! $infrastructure instanceof Infrastructure) {
            $this->components->error('Infrastructure not found.');

            return self::FAILURE;
        }

        if ($infrastructure->status === InfrastructureStatus::Provisioning) {
            $this->components->error('Infrastructure is already being provisioned.');

            return self::FAILURE;
        }

        $infrastructure->update([
            'status' => InfrastructureStatus::Provisioning,
            'provisioning_step' => ProvisioningStep::first(),
            'provisioning_phase' => ProvisioningPhase::Infrastructure,
        ]);

        ProcessProvisioningStep::dispatch($infrastructure);

        $this->components->info("Provisioning started for infrastructure: {$infrastructure->name}");

        return self::SUCCESS;
    }
}
