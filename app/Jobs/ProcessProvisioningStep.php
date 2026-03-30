<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\CreateBastion;
use App\Actions\CreateFirewallForInfrastructure;
use App\Actions\CreateNetworkForInfrastructure;
use App\Actions\GenerateSshKeypairs;
use App\Actions\RegisterSshKeys;
use App\Actions\ScpToBastion;
use App\Actions\WaitForBastion;
use App\Contracts\StepHandler;
use App\Enums\InfrastructureStatus;
use App\Enums\ProvisioningStep;
use App\Exceptions\RetryStepException;
use App\Models\Infrastructure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;
use Throwable;

final class ProcessProvisioningStep implements ShouldQueue
{
    use Queueable;

    private const int RETRY_DELAY_SECONDS = 15;

    private const int MAX_RETRIES_PER_STEP = 40;

    public int $timeout = 600;

    public function __construct(
        public Infrastructure $infrastructure,
        public int $stepRetries = 0,
    ) {}

    public function displayName(): string
    {
        $step = $this->infrastructure->provisioning_step;

        return $step !== null
            ? "ProcessProvisioningStep [{$step->label()}]"
            : 'ProcessProvisioningStep [Complete]';
    }

    public function handle(): void
    {
        $currentStep = $this->infrastructure->provisioning_step;

        if ($currentStep === null || $currentStep->isTerminal()) {
            $this->infrastructure->update([
                'status' => InfrastructureStatus::Healthy,
            ]);

            return;
        }

        $handler = $this->resolveHandler($currentStep);

        try {
            $handler->handle($this->infrastructure);
        } catch (RetryStepException $e) {
            if ($this->stepRetries >= self::MAX_RETRIES_PER_STEP) {
                throw new RuntimeException("Step [{$currentStep->label()}] exceeded maximum retries ({$this->stepRetries}): {$e->getMessage()}");
            }

            self::dispatch($this->infrastructure, $this->stepRetries + 1)
                ->delay(now()->addSeconds(self::RETRY_DELAY_SECONDS));

            return;
        }

        $nextStep = $currentStep->nextStep();

        DB::transaction(function () use ($nextStep): void {
            $updateData = [
                'provisioning_step' => $nextStep,
                'provisioning_phase' => $nextStep?->phase(),
            ];

            if ($nextStep === null || $nextStep->isTerminal()) {
                $updateData['status'] = InfrastructureStatus::Healthy;
            }

            $this->infrastructure->update($updateData);
        });

        if ($nextStep !== null && ! $nextStep->isTerminal()) {
            self::dispatch($this->infrastructure);
        }
    }

    public function failed(Throwable $exception): void
    {
        $this->infrastructure->update([
            'status' => InfrastructureStatus::Failed,
        ]);
    }

    private function resolveHandler(ProvisioningStep $step): StepHandler
    {
        return match ($step) {
            ProvisioningStep::GenerateSshKeypairs => app(GenerateSshKeypairs::class),
            ProvisioningStep::RegisterSshKeys => app(RegisterSshKeys::class),
            ProvisioningStep::CreateNetwork => app(CreateNetworkForInfrastructure::class),
            ProvisioningStep::CreateFirewallRules => app(CreateFirewallForInfrastructure::class),
            ProvisioningStep::CreateBastion => app(CreateBastion::class),
            ProvisioningStep::WaitForBastion => app(WaitForBastion::class),
            ProvisioningStep::ScpToBastion => app(ScpToBastion::class),

            // Steps 8-17 will be implemented in #48 and #52
            default => throw new LogicException("No handler registered for provisioning step: {$step->value}"),
        };
    }
}
