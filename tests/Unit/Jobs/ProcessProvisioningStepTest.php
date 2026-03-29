<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Enums\ProvisioningPhase;
use App\Enums\ProvisioningStep;
use App\Jobs\ProcessProvisioningStep;
use App\Models\Infrastructure;
use Illuminate\Support\Facades\Bus;

test('advances to next step and dispatches itself',
    /**
     * @throws Throwable
     */
    function (): void {
        Bus::fake([ProcessProvisioningStep::class]);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        expect($infrastructure->provisioning_step)->toBe(ProvisioningStep::GenerateSshKeypairs);

        $job = new ProcessProvisioningStep($infrastructure);
        $job->handle();

        $infrastructure->refresh();

        expect($infrastructure->provisioning_step)->toBe(ProvisioningStep::RegisterSshKeys)
            ->and($infrastructure->provisioning_phase)->toBe(ProvisioningPhase::Infrastructure);

        Bus::assertDispatched(ProcessProvisioningStep::class);
    });

test('marks infrastructure healthy on terminal step',
    /**
     * @throws Throwable
     */
    function (): void {
        Bus::fake([ProcessProvisioningStep::class]);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'status' => InfrastructureStatus::Provisioning,
            'provisioning_step' => ProvisioningStep::MarkHealthy,
            'provisioning_phase' => ProvisioningPhase::Configuration,
        ]);

        $job = new ProcessProvisioningStep($infrastructure);
        $job->handle();

        $infrastructure->refresh();

        expect($infrastructure->status)->toBe(InfrastructureStatus::Healthy)
            ->and($infrastructure->provisioning_step)->toBe(ProvisioningStep::MarkHealthy);

        Bus::assertNotDispatched(ProcessProvisioningStep::class);
    });

test('marks infrastructure failed on exception',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        $job = new ProcessProvisioningStep($infrastructure);
        $job->failed(new RuntimeException('Simulated failure'));

        $infrastructure->refresh();

        expect($infrastructure->status)->toBe(InfrastructureStatus::Failed);
    });

test('updates phase when crossing from infrastructure to configuration',
    /**
     * @throws Throwable
     */
    function (): void {
        Bus::fake([ProcessProvisioningStep::class]);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'status' => InfrastructureStatus::Provisioning,
            'provisioning_step' => ProvisioningStep::WaitForNodes,
            'provisioning_phase' => ProvisioningPhase::Infrastructure,
        ]);

        $job = new ProcessProvisioningStep($infrastructure);
        $job->handle();

        $infrastructure->refresh();

        expect($infrastructure->provisioning_step)->toBe(ProvisioningStep::GenerateInventory)
            ->and($infrastructure->provisioning_phase)->toBe(ProvisioningPhase::Configuration);
    });
