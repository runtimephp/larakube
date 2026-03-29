---
adr:
  number: 4
  status: proposed
  date: 2026-03-29
  authors: [Francisco Barrento]
  tags: [kubernetes, infrastructure, architecture, roadmap, ansible, cilium, gateway-api]
  related: [ADR-0001, ADR-0002, ADR-0003]
---

# Kubernetes Architecture, Provisioning Pipeline, and Roadmap

## Context

LaraKube is an internal developer portal (IDP) that provisions and manages Kubernetes clusters across multiple cloud providers. Following a major refactor introducing an HTTP client architecture and Multipass as a local cloud provider (ADR-0003), the project is ready to tackle its first milestone: deploying a Kubernetes cluster end-to-end.

A multi-agent architecture review (Claude Opus, Sonnet, Haiku) was conducted to evaluate the existing ADRs (ADR-001 through ADR-003 from the original Kubernetes planning) and identify gaps, risks, and recommendations. This ADR captures the decisions made after stress-testing the review findings.

### What this ADR overrides

The original Kubernetes ADRs (ADR-001, ADR-002, ADR-003 — the external planning documents, not the repo ADRs) remain as historical records. This ADR supersedes the following decisions from those documents:

| Original decision | This ADR's decision | Reason |
|---|---|---|
| NGINX Ingress Controller | Cilium Gateway API (M2) | Gateway API is GA, better IDP multi-tenancy model |
| Flannel CNI (Cilium later) | Cilium from day 1 | CNI migration is disruptive; Cilium provides NetworkPolicy, kube-proxy replacement, and Gateway API built-in |
| DigitalOcean via DOKS (managed) | All clusters self-managed kubeadm | Eliminates managed-vs-self-managed branching; same Ansible playbooks everywhere |
| DigitalOcean in M1 | DigitalOcean deferred to M9 | Developer-facing features (app deployment, secrets) prioritized over cloud provider breadth |

## Decision

### 1. Provisioning Architecture

#### 1.1 Ansible as Configuration Management Tool

All cluster configuration is managed by Ansible. Ansible playbooks live in the LaraKube repository under a `playbooks/` directory. Playbooks are provider-agnostic — the same roles work on Multipass and Hetzner, parameterized by inventory variables.

#### 1.2 Permanent Bastion Server

Every infrastructure provisions a permanent bastion server. The bastion serves dual purpose:

- **Entry point**: SSH jump host for cluster access
- **Operations hub**: Runs Ansible, kubectl, Helm, and any cluster management tooling

The bastion is the only server LaraKube SSHes into directly. All cluster node operations go through the bastion via Ansible.

#### 1.3 Bastion Bootstrap — Cloud-Init + SCP

The bastion is bootstrapped in two phases:

1. **Cloud-init** installs base tools during VM creation: Ansible, Python, kubectl, Helm. All three providers (Multipass, Hetzner, DigitalOcean) support cloud-init (`--cloud-init`, `user_data`).
2. **SCP from LaraKube** pushes project-specific files after the bastion is ready: Ansible playbooks, SSH keys for cluster nodes, Ansible inventory.

#### 1.4 Ansible Execution via Queued Jobs

LaraKube triggers Ansible from Laravel queued jobs:

1. A queued job SSHes into the bastion
2. Runs `ansible-playbook` synchronously (from the job's perspective)
3. Updates `KubernetesCluster` status when complete or on failure

The web process is never blocked. Status is coarse (started → done/failed) — real-time task-level progress via Ansible callback plugins is a future enhancement.

### 2. SSH Key Management

#### 2.1 Two Keypairs Per Infrastructure

| Keypair | Stored in LaraKube DB | Lives on | Purpose |
|---|---|---|---|
| Bastion key | Private key (encrypted) + public key | LaraKube → bastion | LaraKube SSHes into bastion |
| Node key | Public key only | Bastion → cluster nodes | Ansible SSHes into nodes |

The node key's private key is generated on the bastion during cloud-init and never leaves the bastion. If LaraKube's database is compromised, the attacker can reach the bastion but not cluster nodes directly.

#### 2.2 Provider-Aware Key Registration

- **Hetzner/DigitalOcean**: Public keys are registered via the cloud provider's SSH key API and referenced by ID at server creation time.
- **Multipass**: Public keys are injected via cloud-init YAML (`ssh_authorized_keys`).

#### 2.3 SshKey Model Changes

The existing `SshKey` model gains:

- `private_key` (encrypted, nullable) — populated only for bastion keypair
- `purpose` enum — `bastion` or `node`

The relationship stays on `Infrastructure`. Both keypairs belong to the infrastructure.

### 3. Service Contracts

Three new contracts following the existing `CloudProviderService`/`ServerService` pattern:

| Contract | Methods | Multipass implementation |
|---|---|---|
| `SshKeyService` | `register()`, `list()`, `delete()` | No-op (cloud-init handles key injection) |
| `NetworkService` | `create()`, `list()`, `find()`, `delete()` | No-op (Multipass uses shared bridge network) |
| `FirewallService` | `create()`, `addRule()`, `list()`, `find()`, `delete()` | No-op (no firewall concept in Multipass) |

`CloudProviderFactory` gains: `makeSshKeyService()`, `makeNetworkService()`, `makeFirewallService()`.

Each contract gets implementations per provider (Hetzner, Multipass) plus InMemory test doubles.

### 4. Provisioning Pipeline

#### 4.1 State Machine

A `ProvisioningStep` enum tracks the current step. A single `ProcessProvisioningStep` queued job reads the current step, executes it, advances the state, and dispatches itself for the next step. On failure, the state records exactly where it stopped. The pipeline is resumable by re-dispatching the same job.

The 17-step pipeline:

```
 1. Generate SSH keypairs (bastion-key + node-key)
 2. Register public keys with cloud provider (SshKeyService)
 3. Create network/VPC (NetworkService)
 4. Create firewall rules (FirewallService)
 5. Create bastion server (ServerService + cloud-init)
 6. Wait for bastion to be ready
 7. SCP to bastion: playbooks, node private key, Ansible inventory
 8. Create control plane nodes (ServerService + node-key)
 9. Create worker nodes (ServerService + node-key)
10. Wait for all nodes to be ready
11. Generate Ansible inventory from node IPs
12. SCP updated inventory to bastion
13. SSH to bastion → run ansible-playbook (kubeadm init, join, Cilium)
14. SSH to bastion → retrieve kubeconfig
15. Store kubeconfig (encrypted) in KubernetesCluster model
16. Health check (kubectl get nodes via bastion)
17. Update KubernetesCluster status → Healthy
```

#### 4.2 Selective Rollback

The pipeline splits into two phases with different failure strategies:

| Phase | Steps | On failure (after retries) |
|---|---|---|
| **Infrastructure** | 1–10 | Full rollback — destroy all created resources in reverse order |
| **Configuration** | 11–17 | No rollback — VMs are fine, the problem is configuration. Mark as `Failed`, allow re-trigger from failed step |

Each step in the infrastructure phase gets N retries before rollback triggers. Configuration failures (Ansible errors, kubeadm timeouts) are retryable without destroying working infrastructure.

### 5. Kubernetes Stack

#### 5.1 CNI — Cilium from Day 1

Cilium in kube-proxy replacement mode (`kubeadm init --skip-phases=addon/kube-proxy`). No Flannel.

Cilium provides:
- NetworkPolicy enforcement (required for future multi-tenancy)
- Hubble observability
- WireGuard transparent encryption
- Gateway API implementation (M2)
- No kube-proxy (one less component)

#### 5.2 Gateway API — Deferred to M2

Cilium's built-in Gateway API implementation. Enabled via Helm value when M2 begins. No separate Envoy Gateway or NGINX deployment needed.

#### 5.3 Cluster Topologies

Ansible playbooks support both topologies from day 1:

| Topology | Control plane | HA mechanism | Use case |
|---|---|---|---|
| `single_cp` | 1 node | None | Multipass local dev |
| `ha` | 3 nodes | kube-vip (ARP mode, floating VIP) | Hetzner production |

An Ansible variable (`topology`) drives the logic. `kubeadm init` is the same; HA adds `kubeadm join --control-plane` on CP2/CP3 and kube-vip as a static pod.

#### 5.4 No DOKS

All clusters are self-managed kubeadm, including future DigitalOcean support. This eliminates the managed-vs-self-managed divergence in the provisioning pipeline and ensures the same Ansible playbooks work across all providers.

### 6. KubernetesCluster Model Changes

New fields on the existing model:

| Field | Type | Purpose |
|---|---|---|
| `kubeconfig` | encrypted text | Admin kubeconfig retrieved from bastion |
| `api_endpoint` | string | API server URL (kube-vip VIP or CP node IP) |
| `pod_cidr` | string | e.g., `10.244.0.0/16` |
| `service_cidr` | string | e.g., `10.96.0.0/12` |
| `cni` | string | `cilium` |
| `provisioning_step` | string/enum | Current step in state machine |
| `provisioning_phase` | enum | `infrastructure` or `configuration` |
| `topology` | enum | `single_cp` or `ha` |

### 7. Container Registry

The container registry is an organization-level concern, not a cluster concern.

- `ContainerRegistry` model on `Organization` (type, url, credentials)
- Supported types: Harbor, Nexus, ghcr.io, Docker Hub, ECR, GCR, JFrog
- LaraKube generates Kubernetes `imagePullSecrets` and injects them into namespaces
- LaraKube does not host a registry — it integrates with the org's chosen registry

### 8. Application Build Pipeline (M3)

#### 8.1 LaraKube-Maintained Runtime Images

LaraKube provides opinionated base Docker images:

- `larakube/php84-laravel` — PHP 8.4 + Laravel runtime
- `larakube/node22` — Node.js 22 runtime
- `larakube/static` — Static site (nginx)

Additional runtimes added as needed.

#### 8.2 Developer Customization

Developers specify:
- **Build scripts** (e.g., `composer install`, `npm run build`) — run during image build
- **Deploy scripts** (e.g., `php artisan migrate`, `php artisan config:cache`) — run at deployment time

LaraKube generates a Dockerfile layering the developer's scripts onto the base runtime image.

#### 8.3 Repo Detection

File-based detection suggests a runtime (e.g., `composer.json` → PHP/Laravel). The developer confirms or overrides. No deep buildpack-style analysis.

#### 8.4 Kaniko In-Cluster Builds

Image builds run as Kubernetes Jobs using Kaniko on worker nodes. No Docker daemon or privileged containers needed. Kaniko pushes directly to the org's configured registry.

#### 8.5 Trivy Security Scanning

Every image is scanned by Trivy before push. If vulnerabilities exceed the configured threshold, the deployment is blocked and the developer is notified. Baked into the build pipeline from M3.

### 9. Milestone Roadmap

| Milestone | Title | Key deliverables |
|---|---|---|
| **M1** | Deploy a Kubernetes Cluster | Full provisioning pipeline, state machine, selective rollback, bastion + Ansible, Cilium CNI, single-CP + HA topologies, Multipass + Hetzner |
| **M2** | Gateway API + TLS | Cilium Gateway API, cert-manager, Let's Encrypt, working HTTPRoute |
| **M3** | Applications + Build Pipeline | Application model, container registry config, runtime images, Kaniko builds, Trivy scanning, repo detection |
| **M4** | Deploy Applications | Deployment API, manifest generation (Deployment + Service + HTTPRoute), deploy via bastion, status tracking |
| **M5** | Secrets Management | External Secrets Operator + Vault, developer secrets API |
| **M6** | Multi-tenancy + RBAC + Policies | Namespace isolation, resource quotas, Kyverno policies, scoped access |
| **M7** | Monitoring + Observability | Prometheus + Grafana + Loki via Ansible, per-namespace dashboards |
| **M8** | GitOps + ArgoCD | ArgoCD as cluster add-on, manifest Git commits, deployment history |
| **M9** | Additional Cloud Providers | DigitalOcean (self-managed kubeadm), provider pattern documented |
| **M10** | Autoscaling + Disaster Recovery | Cluster autoscaler, HPA, etcd backup automation, restore procedures |

## Consequences

### Positive

- **Full automation**: Developers run one command and get a working cluster
- **Provider-agnostic pipeline**: Same Ansible playbooks, same state machine, across Multipass and Hetzner
- **Secure by default**: Two-keypair SSH model, Cilium NetworkPolicies, Trivy scanning
- **Resumable provisioning**: State machine allows retry from failure point, not restart from zero
- **Opinionated stack**: Cilium + Gateway API + kubeadm gives consistency across all clusters
- **IDP-first design**: App model, runtime images, and build pipeline designed for developer self-service

### Negative

- **Cilium complexity**: Cilium is more complex to debug than Flannel when things go wrong
- **Ansible dependency**: The bastion becomes a single point of operational access — if it's down, cluster operations are blocked
- **No managed K8s option**: Self-managed kubeadm on all providers means full lifecycle responsibility (upgrades, etcd backups, cert rotation)
- **Large M1 scope**: The provisioning pipeline, state machine, and Ansible playbooks are significant engineering effort
- **10 milestones**: Long roadmap before the full IDP vision is realized

### Risks

- **Bastion as SPOF**: If the bastion VM is lost, cluster operations are blocked until a new one is provisioned. Mitigation: etcd backups stored externally, bastion can be rebuilt from cloud-init + SCP.
- **Cilium version compatibility**: Cilium releases may lag behind Kubernetes releases. Mitigation: pin Cilium Helm chart versions, test upgrades in Multipass before Hetzner.
- **Ansible playbook drift**: Playbooks must stay in sync with LaraKube's provisioning pipeline expectations. Mitigation: integration tests that run the full pipeline on Multipass.
- **Cloud-init variance**: Cloud-init behavior may differ subtly across providers. Mitigation: test cloud-init templates on each provider before relying on them.
- **Selective rollback gaps**: Some infrastructure failures may leave resources in a state that's hard to detect and clean up. Mitigation: resource tagging and reconciliation checks before rollback.

## Compliance

- All provisioning pipeline changes must include Multipass integration tests
- Ansible playbook changes must be tested on both single-CP and HA topologies
- New service contract implementations must include InMemory test doubles
- Security scanning (Trivy) threshold violations must block deployment — no override without explicit approval
- SSH private keys must be encrypted at rest in the database

## Notes

### Alternatives Considered

| Decision | Alternative | Reason rejected |
|---|---|---|
| Cilium CNI | Flannel (defer Cilium) | CNI migration is disruptive; Flannel lacks NetworkPolicy |
| Cilium Gateway API | Envoy Gateway (standalone) | Extra deployment; Cilium's built-in gateway is zero-cost |
| Ansible on bastion | SSH from LaraKube directly | Couples LaraKube to SSH libraries; bastion is cleaner |
| Queued jobs | Ansible callback plugins | Added complexity; coarse status is sufficient for M1 |
| Permanent bastion | Ephemeral bastion | Day-2 operations (scaling, upgrades) need persistent access |
| Two keypairs | Single keypair | Security boundary — DB compromise shouldn't expose cluster nodes |
| Self-managed DO | DOKS (managed) | Eliminates managed-vs-self-managed pipeline branching |
| Kaniko | Docker-on-bastion | Bastion should stay lean; Kaniko is cloud-native, no daemon |
| Trivy | Nexus Pro scanning | Trivy is free, registry-agnostic, runs as K8s Job |

### Multi-Agent Review

This ADR was informed by a parallel review from three AI agents (Claude Opus, Sonnet, Haiku). The full reports are archived at `agents/counselors/1774771656-architecture-review-kubernetes-setup-fo/`. Key disagreements resolved:

- **Cilium vs Flannel timing**: Opus and Sonnet recommended Cilium day 1; Haiku recommended Flannel first. Cilium day 1 was chosen.
- **Gateway API implementation**: Opus recommended Envoy Gateway (decoupled); Sonnet recommended Cilium Gateway (zero-cost). Cilium Gateway was chosen.
- **Local testing tools**: Opus recommended Multipass only; Sonnet and Haiku recommended adding kind/k3d. Multipass only was chosen for provisioning; kind may be added for CI later.
