<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\CreateBastion;
use App\Actions\CreateControlPlaneNodes;
use App\Actions\CreateFirewallForInfrastructure;
use App\Actions\CreateNetworkForInfrastructure;
use App\Actions\CreateWorkerNodes;
use App\Actions\GenerateInventory;
use App\Actions\GenerateSshKeypairs;
use App\Actions\HealthCheck;
use App\Actions\MarkHealthy;
use App\Actions\RegisterSshKeys;
use App\Actions\RetrieveKubeconfig;
use App\Actions\RunAnsible;
use App\Actions\ScpInventoryToBastion;
use App\Actions\ScpToBastion;
use App\Actions\StoreKubeconfig;
use App\Actions\WaitForBastion;
use App\Actions\WaitForNodes;
use App\Contracts\StepHandler;
use App\Enums\InfrastructureStatus;
use App\Enums\ProvisioningStep;
use App\Exceptions\RetryStepException;
use App\Models\Infrastructure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class ProcessProvisioningStep implements ShouldQueue
{
    use Queueable;

    private const int RETRY_DELAY_SECONDS = 15;

    private const int MAX_RETRIES_PER_STEP = 40;

    public int $timeout = 1800;

    public function __construct(
        public Infrastructure $infrastructure,
        public int $stepRetries = 0,
    ) {
        $this->onQueue($this->resolveQueue());
    }

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

        Log::info("[{$this->infrastructure->name}] Step: {$currentStep->label()} — starting");

        try {
            $handler->handle($this->infrastructure);
            Log::info("[{$this->infrastructure->name}] Step: {$currentStep->label()} — completed");
        } catch (RetryStepException $e) {
            Log::info("[{$this->infrastructure->name}] Step: {$currentStep->label()} — retry ({$this->stepRetries}): {$e->getMessage()}");
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
                $updateData['status'] = InfrastructureStatus::Healthy; // @codeCoverageIgnore
            }

            $this->infrastructure->update($updateData);
        });

        if ($nextStep !== null && ! $nextStep->isTerminal()) {
            self::dispatch($this->infrastructure);
        }
    }

    public function failed(Throwable $exception): void
    {
        $step = $this->infrastructure->provisioning_step;

        Log::error("[{$this->infrastructure->name}] Step: {$step?->label()} — FAILED: {$exception->getMessage()}");

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
            ProvisioningStep::CreateControlPlaneNodes => app(CreateControlPlaneNodes::class),
            ProvisioningStep::CreateWorkerNodes => app(CreateWorkerNodes::class),
            ProvisioningStep::WaitForNodes => app(WaitForNodes::class),
            ProvisioningStep::GenerateInventory => app(GenerateInventory::class),
            ProvisioningStep::ScpInventory => app(ScpInventoryToBastion::class),
            ProvisioningStep::RunAnsible => app(RunAnsible::class),
            ProvisioningStep::RetrieveKubeconfig => app(RetrieveKubeconfig::class),
            ProvisioningStep::StoreKubeconfig => app(StoreKubeconfig::class),
            ProvisioningStep::HealthCheck => app(HealthCheck::class),
            ProvisioningStep::MarkHealthy => app(MarkHealthy::class),
        };
    }

    private function resolveQueue(): string
    {
        $step = $this->infrastructure->provisioning_step;

        if ($step === null) {
            return 'infrastructure';
        }

        return match ($step) {
            ProvisioningStep::CreateBastion,
            ProvisioningStep::CreateControlPlaneNodes,
            ProvisioningStep::CreateWorkerNodes,
            ProvisioningStep::RunAnsible => 'infrastructure-long',
            default => 'infrastructure',
        };
    }
}
