<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\InfrastructureStatus;
use App\Enums\ProvisioningPhase;
use App\Enums\ProvisioningStep;
use App\Jobs\ProcessProvisioningStep;
use App\Models\Infrastructure;

final class ProvisionInfrastructureCommand extends AuthenticatedCommand
{
    protected $signature = 'infrastructure:provision';

    protected $description = 'Start provisioning a Kubernetes cluster for the selected infrastructure';

    protected bool $requiresOrganization = true;

    protected bool $requiresInfrastructure = true;

    public function handleCommand(): int
    {
        /** @var Infrastructure|null $infrastructure */
        $infrastructure = Infrastructure::query()->find($this->infrastructure->id);

        if ($infrastructure === null) {
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
