<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

final class ProcessProvisioningStep implements ShouldQueue
{
    use Queueable;

    public function __construct(public Infrastructure $infrastructure) {}

    public function handle(): void
    {
        $currentStep = $this->infrastructure->provisioning_step;

        if ($currentStep === null || $currentStep->isTerminal()) {
            $this->infrastructure->update([
                'status' => InfrastructureStatus::Healthy,
            ]);

            return;
        }

        // TODO: Execute the actual step logic (delegated to step handlers in #47/#48/#52)

        $nextStep = $currentStep->nextStep();

        $this->infrastructure->update([
            'provisioning_step' => $nextStep,
            'provisioning_phase' => $nextStep?->phase(),
        ]);

        if ($nextStep !== null) {
            self::dispatch($this->infrastructure);
        }
    }

    public function failed(Throwable $exception): void
    {
        $this->infrastructure->update([
            'status' => InfrastructureStatus::Failed,
        ]);
    }
}
