<?php

declare(strict_types=1);

use App\Enums\ProvisioningPhase;
use App\Enums\ProvisioningStep;

test('cases returns all 17 steps', function (): void {
    expect(ProvisioningStep::cases())->toHaveCount(17);
});

test('infrastructure phase steps', function (): void {
    $infrastructureSteps = [
        ProvisioningStep::GenerateSshKeypairs,
        ProvisioningStep::RegisterSshKeys,
        ProvisioningStep::CreateNetwork,
        ProvisioningStep::CreateFirewallRules,
        ProvisioningStep::CreateBastion,
        ProvisioningStep::WaitForBastion,
        ProvisioningStep::ScpToBastion,
        ProvisioningStep::CreateControlPlaneNodes,
        ProvisioningStep::CreateWorkerNodes,
        ProvisioningStep::WaitForNodes,
    ];

    foreach ($infrastructureSteps as $step) {
        expect($step->phase())->toBe(ProvisioningPhase::Infrastructure, "Step {$step->value} should be infrastructure phase");
    }
});

test('configuration phase steps', function (): void {
    $configurationSteps = [
        ProvisioningStep::GenerateInventory,
        ProvisioningStep::ScpInventory,
        ProvisioningStep::RunAnsible,
        ProvisioningStep::RetrieveKubeconfig,
        ProvisioningStep::StoreKubeconfig,
        ProvisioningStep::HealthCheck,
        ProvisioningStep::MarkHealthy,
    ];

    foreach ($configurationSteps as $step) {
        expect($step->phase())->toBe(ProvisioningPhase::Configuration, "Step {$step->value} should be configuration phase");
    }
});

test('next step returns correct sequence', function (): void {
    expect(ProvisioningStep::GenerateSshKeypairs->nextStep())->toBe(ProvisioningStep::RegisterSshKeys)
        ->and(ProvisioningStep::RegisterSshKeys->nextStep())->toBe(ProvisioningStep::CreateNetwork)
        ->and(ProvisioningStep::CreateNetwork->nextStep())->toBe(ProvisioningStep::CreateFirewallRules)
        ->and(ProvisioningStep::CreateFirewallRules->nextStep())->toBe(ProvisioningStep::CreateBastion)
        ->and(ProvisioningStep::CreateBastion->nextStep())->toBe(ProvisioningStep::WaitForBastion)
        ->and(ProvisioningStep::WaitForBastion->nextStep())->toBe(ProvisioningStep::ScpToBastion)
        ->and(ProvisioningStep::ScpToBastion->nextStep())->toBe(ProvisioningStep::CreateControlPlaneNodes)
        ->and(ProvisioningStep::CreateControlPlaneNodes->nextStep())->toBe(ProvisioningStep::CreateWorkerNodes)
        ->and(ProvisioningStep::CreateWorkerNodes->nextStep())->toBe(ProvisioningStep::WaitForNodes)
        ->and(ProvisioningStep::WaitForNodes->nextStep())->toBe(ProvisioningStep::GenerateInventory)
        ->and(ProvisioningStep::GenerateInventory->nextStep())->toBe(ProvisioningStep::ScpInventory)
        ->and(ProvisioningStep::ScpInventory->nextStep())->toBe(ProvisioningStep::RunAnsible)
        ->and(ProvisioningStep::RunAnsible->nextStep())->toBe(ProvisioningStep::RetrieveKubeconfig)
        ->and(ProvisioningStep::RetrieveKubeconfig->nextStep())->toBe(ProvisioningStep::StoreKubeconfig)
        ->and(ProvisioningStep::StoreKubeconfig->nextStep())->toBe(ProvisioningStep::HealthCheck)
        ->and(ProvisioningStep::HealthCheck->nextStep())->toBe(ProvisioningStep::MarkHealthy);
});

test('terminal step returns null', function (): void {
    expect(ProvisioningStep::MarkHealthy->nextStep())->toBeNull();
});

test('is terminal returns true only for last step', function (): void {
    expect(ProvisioningStep::MarkHealthy->isTerminal())->toBeTrue()
        ->and(ProvisioningStep::GenerateSshKeypairs->isTerminal())->toBeFalse()
        ->and(ProvisioningStep::HealthCheck->isTerminal())->toBeFalse();
});

test('first returns the initial step', function (): void {
    expect(ProvisioningStep::first())->toBe(ProvisioningStep::GenerateSshKeypairs);
});

test('label returns human readable labels', function (): void {
    expect(ProvisioningStep::GenerateSshKeypairs->label())->toBe('Generate SSH Keypairs')
        ->and(ProvisioningStep::RunAnsible->label())->toBe('Run Ansible')
        ->and(ProvisioningStep::MarkHealthy->label())->toBe('Mark Healthy');
});
