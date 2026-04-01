---
adr:
  number: 5
  status: proposed
  date: 2026-04-01
  authors: [Francisco Barrento]
  tags: [kubernetes, capi, cluster-api, infrastructure, architecture]
  related: [ADR-0004, ADR-0006, ADR-0007, ADR-0008]
---

# Adopt Cluster API (CAPI) as Cluster Lifecycle Engine

## Context

ADR-0004 established a self-managed provisioning pipeline: a 17-step state machine, permanent bastion servers, Ansible playbooks, SSH-based orchestration, and per-provider service contracts (`ServerService`, `SshKeyService`, `NetworkService`, `FirewallService`, `NatGatewayService`). While functional for deploying individual clusters, this architecture does not scale to a multi-tenant PaaS managing hundreds of clusters across providers.

The core problems with the ADR-0004 approach:

- **Kuven owns every line of provisioning logic** — SSH, cloud-init, Ansible, rollback, node health remediation, upgrades. Each is a feature Kuven must build and maintain.
- **Day-2 operations are unbounded scope** — scaling, upgrades, node replacement, certificate rotation, etcd backups all require bespoke implementation per provider.
- **The bastion is a single point of failure** — all cluster operations route through it. If lost, operations are blocked until rebuilt.
- **Provider contracts couple Kuven to cloud API details** — Kuven directly manages VMs, networks, firewalls, and SSH keys through 6 service contracts with 3 provider implementations each.

Cluster API (CAPI) is a Kubernetes-native project that provides declarative APIs for provisioning, upgrading, and operating Kubernetes clusters. Infrastructure providers (CAPH for Hetzner, CAPDO for DigitalOcean, CAPA for AWS) handle provider-specific resource management. CAPI's reconciliation loop handles day-2 operations automatically.

## Decision

Kuven adopts Cluster API (CAPI) as its cluster lifecycle engine. This supersedes ADR-0004.

### What CAPI Owns

- VM provisioning via infrastructure providers (CAPH, CAPDO, CAPA)
- Node bootstrapping via bootstrap providers (kubeadm)
- Control plane lifecycle via control plane providers (KubeadmControlPlane)
- Machine health checks and automatic remediation
- Rolling upgrades (Kubernetes version, machine images)
- Scaling (MachineDeployment replicas)
- Network, firewall, and SSH key management (handled internally by infrastructure providers)

### What Kuven Owns

- Developer UX — UI, API, CLI
- Multi-tenancy — organization isolation, RBAC, billing
- CAPI manifest generation — translating user intent into Cluster, MachineDeployment, and KubeadmControlPlane resources
- Cluster status monitoring — polling CAPI conditions and surfacing to the UI
- Kubeconfig management — retrieving and storing encrypted kubeconfigs
- Add-on orchestration — CNI (Cilium), Gateway API, monitoring stacks via ClusterResourceSets
- Management cluster operations — bootstrap, upgrades, health monitoring

### Supported Infrastructure Providers

| Provider | CAPI Provider | Status |
|----------|--------------|--------|
| Hetzner | CAPH (cluster-api-provider-hetzner) | M1 |
| DigitalOcean | CAPDO (cluster-api-provider-digitalocean) | Roadmap |
| AWS | CAPA (cluster-api-provider-aws) | Roadmap |

Each workload cluster uses a single provider. No cross-provider node topologies.

### Operational Model

After the management cluster exists (see ADR-0007), all ongoing operations are pure HTTP calls to the Kubernetes API:

1. Kuven generates CAPI manifests from user configuration
2. Kuven applies manifests to the management cluster via a PHP Kubernetes client
3. CAPI reconciles — provisions infrastructure, bootstraps nodes, installs components
4. Kuven polls Cluster conditions (`Ready`, `InfrastructureReady`, `ControlPlaneReady`) for status

This runs on standard Laravel Cloud queue workers with no binary dependencies. The PHP Kubernetes client makes HTTP calls to the management cluster API — no `kubectl`, `clusterctl`, or SSH required.

### No Custom State Machine

The `ProvisioningStep` enum (17 steps) and `ProvisioningPhase` enum from ADR-0004 are removed. CAPI's reconciliation loop replaces the state machine. Kuven maps CAPI's Cluster conditions directly to `InfrastructureStatus`:

| CAPI Condition | InfrastructureStatus |
|---------------|---------------------|
| Provisioning (no Ready condition) | Provisioning |
| Ready=True | Healthy |
| Ready=False, reason=degraded | Degraded |
| Ready=False, reason=error | Failed |
| Cluster deleted | Destroyed |

### Impact on Existing Codebase

The following components from ADR-0004 become obsolete:

- **Remove**: `ProvisioningStep` enum, `ProvisioningPhase` enum, `ProcessProvisioningStep` job
- **Remove**: `BastionSshExecutor`, `CloudInitGenerator`, `SshKeyGenerator`
- **Remove**: Write methods from provider contracts (`create()`, `delete()`, `register()`, `addRule()`)
- **Remove**: Provisioning actions (`CreateBastion`, `RunAnsible`, `ScpToBastion`, `GenerateInventory`, `RetrieveKubeconfig`, `CreateControlPlaneNodes`, `CreateWorkerNodes`, `WaitForBastion`, `WaitForNodes`, etc.)
- **Keep**: `InfrastructureStatus` enum (maps to CAPI conditions)
- **Keep**: `CloudProviderType` enum (identifies provider for manifest generation)
- **Keep**: Read-only query methods on provider contracts (`list()`, `find()`) — see ADR-0009
- **Keep**: Models (`CloudProvider`, `Infrastructure`, `KubernetesCluster`, `Server`)

### New Components

- **CAPI manifest generation layer** — per-provider template generation for Cluster, MachineDeployment, KubeadmControlPlane, and infrastructure-specific resources
- **PHP Kubernetes client integration** — for applying manifests and polling status
- **Cluster status poller** — scheduled job that watches CAPI conditions and updates Kuven's database

## Consequences

### Positive

- **Massive scope reduction** — CAPI providers handle VM provisioning, networking, SSH, node bootstrap, and day-2 operations. Kuven focuses on UX and multi-tenancy.
- **Day-2 operations for free** — upgrades, scaling, machine health checks, node replacement are built into CAPI.
- **Declarative model** — desired state reconciliation is more robust than a linear step-by-step pipeline.
- **Provider ecosystem** — new cloud providers can be added by installing a CAPI provider, not by implementing 6 service contracts.
- **No bastion SPOF** — CAPI operates from the management cluster, not a per-infrastructure bastion VM.

### Negative

- **Existing code loss** — ~2,500 lines of provisioning code (actions, contracts, services, test doubles) become obsolete.
- **New dependency** — Kuven depends on CAPI and its provider ecosystem. CAPI version compatibility, provider maturity, and upstream bugs become Kuven's concern.
- **Management cluster overhead** — requires operating a Kubernetes cluster just to manage other clusters.
- **PHP Kubernetes client maturity** — PHP Kubernetes client libraries have less community support than Go or Python equivalents.
- **CAPI learning curve** — the team must understand CAPI's resource model, conditions, and provider-specific CRDs.

### Risks

- **CAPI provider maturity** — CAPH and CAPDO are less mature than CAPA. Provider-specific bugs may require workarounds or upstream contributions.
- **PHP K8s client limitations** — CAPI uses custom resources (CRDs). The PHP client must support applying arbitrary manifests, not just core Kubernetes resources.
- **Management cluster availability** — if the management cluster goes down, no new clusters can be provisioned and status polling stops. Mitigation: standard Kubernetes HA (3 control plane nodes), monitoring, and alerting.
- **CAPI version upgrades** — CAPI releases may require manifest format changes. Mitigation: version pinning and staged rollouts.

## Compliance

- All CAPI manifest generation must be tested with provider-specific fixture data
- PHP Kubernetes client integration must include retry and timeout handling
- Cluster status polling must not overwhelm the management cluster API — use appropriate intervals and watch semantics where supported
- Existing provisioning code must be annotated with `@see ADR-0005` before removal

## Notes

### Alternatives Considered

| Decision | Alternative | Reason rejected |
|----------|-----------|----------------|
| CAPI | Continue bastion/Ansible (ADR-0004) | Does not scale to multi-tenant PaaS; unbounded day-2 ops scope |
| CAPI | Crossplane | Crossplane manages infrastructure but does not manage Kubernetes cluster lifecycle directly |
| CAPI | Terraform via PHP | Adds a binary dependency; Terraform state management is complex in a multi-tenant context |
| CAPI | Managed Kubernetes (EKS, GKE, DOKS) | Limits provider choice; different APIs per provider; no self-managed option |

### PHP Kubernetes Client Candidates

- `renoki-co/php-k8s` — actively maintained, supports CRD operations
- `maclof/kubernetes-client` — simpler API, may lack CRD support
- Raw HTTP client (Guzzle/Laravel HTTP) — maximum flexibility, no library dependency

Evaluation is a prerequisite for implementation.
