<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * @see ADR-0005 — Superseded by CAPI; scheduled for removal
 */
enum ProvisioningStep: string
{
    // Infrastructure phase (steps 1-10)
    case GenerateSshKeypairs = 'generate_ssh_keypairs';
    case RegisterSshKeys = 'register_ssh_keys';
    case CreateNetwork = 'create_network';
    case CreateFirewallRules = 'create_firewall_rules';
    case CreateBastion = 'create_bastion';
    case WaitForBastion = 'wait_for_bastion';
    case ScpToBastion = 'scp_to_bastion';
    case CreateControlPlaneNodes = 'create_control_plane_nodes';
    case CreateWorkerNodes = 'create_worker_nodes';
    case WaitForNodes = 'wait_for_nodes';

    // Configuration phase (steps 11-17)
    case GenerateInventory = 'generate_inventory';
    case ScpInventory = 'scp_inventory';
    case RunAnsible = 'run_ansible';
    case RetrieveKubeconfig = 'retrieve_kubeconfig';
    case StoreKubeconfig = 'store_kubeconfig';
    case HealthCheck = 'health_check';
    case MarkHealthy = 'mark_healthy';

    public static function first(): self
    {
        return self::GenerateSshKeypairs;
    }

    public function phase(): ProvisioningPhase
    {
        return match ($this) {
            self::GenerateSshKeypairs,
            self::RegisterSshKeys,
            self::CreateNetwork,
            self::CreateFirewallRules,
            self::CreateBastion,
            self::WaitForBastion,
            self::ScpToBastion,
            self::CreateControlPlaneNodes,
            self::CreateWorkerNodes,
            self::WaitForNodes => ProvisioningPhase::Infrastructure,

            self::GenerateInventory,
            self::ScpInventory,
            self::RunAnsible,
            self::RetrieveKubeconfig,
            self::StoreKubeconfig,
            self::HealthCheck,
            self::MarkHealthy => ProvisioningPhase::Configuration,
        };
    }

    public function nextStep(): ?self
    {
        $cases = self::cases();
        $index = array_search($this, $cases, true);

        return $cases[$index + 1] ?? null;
    }

    public function isTerminal(): bool
    {
        return $this === self::MarkHealthy;
    }

    public function label(): string
    {
        return match ($this) {
            self::GenerateSshKeypairs => 'Generate SSH Keypairs',
            self::RegisterSshKeys => 'Register SSH Keys',
            self::CreateNetwork => 'Create Network',
            self::CreateFirewallRules => 'Create Firewall Rules',
            self::CreateBastion => 'Create Bastion',
            self::WaitForBastion => 'Wait For Bastion',
            self::ScpToBastion => 'SCP To Bastion',
            self::CreateControlPlaneNodes => 'Create Control Plane Nodes',
            self::CreateWorkerNodes => 'Create Worker Nodes',
            self::WaitForNodes => 'Wait For Nodes',
            self::GenerateInventory => 'Generate Inventory',
            self::ScpInventory => 'SCP Inventory',
            self::RunAnsible => 'Run Ansible',
            self::RetrieveKubeconfig => 'Retrieve Kubeconfig',
            self::StoreKubeconfig => 'Store Kubeconfig',
            self::HealthCheck => 'Health Check',
            self::MarkHealthy => 'Mark Healthy',
        };
    }
}
