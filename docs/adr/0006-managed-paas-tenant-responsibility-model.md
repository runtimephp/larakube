---
adr:
  number: 6
  status: proposed
  date: 2026-04-01
  authors: [Francisco Barrento]
  tags: [paas, tenancy, responsibility, architecture]
  related: [ADR-0005, ADR-0007, ADR-0008]
---

# Kuven as Managed PaaS — Tenant Responsibility Model

## Context

With the adoption of CAPI (ADR-0005), Kuven needs a clear model for what the platform manages versus what tenants are responsible for. This decision shapes the entire product: pricing, onboarding, support surface, and the degree of Kubernetes knowledge required from tenants.

Two models exist in the market:

1. **Self-service toolkit** — tenants interact with cluster infrastructure directly (kubectl, dashboards, CAPI). Kuven is a management layer. Examples: Rancher, Lens.
2. **Managed PaaS** — tenants never see Kubernetes internals. They describe what they want (a cluster, an app deployment) and the platform handles everything. Examples: Heroku, Render, Porter.

## Decision

Kuven is a **managed PaaS**. Tenants provide cloud provider credentials. Kuven operates the full cluster lifecycle on their behalf.

### What Tenants Control

- **Cloud provider credentials** — tenants connect their Hetzner, DigitalOcean, or AWS accounts. Kuven provisions workload clusters into the tenant's cloud account using their API tokens.
- **Cluster configuration** — region, node count, machine sizes, Kubernetes version.
- **Application deployments** — what runs on their clusters.
- **Cloud spend** — workload cluster infrastructure costs are on the tenant's cloud account.

### What Tenants Never See

- CAPI resources, management cluster, or any Kubernetes control plane internals
- `kubectl` access to the management cluster
- CAPI manifests, CRDs, or reconciliation status
- Infrastructure provider details (CAPH, CAPDO)

### What Kuven Operates

- **Management cluster** — Kuven's own infrastructure, invisible to tenants (ADR-0007)
- **Cluster lifecycle** — provisioning, upgrades, scaling, health monitoring, node replacement
- **Add-on management** — CNI (Cilium), ingress, monitoring, security policies
- **Kubeconfig delivery** — tenants receive a scoped kubeconfig for their workload cluster if they need direct access

### Credential Model

Tenants register their cloud provider credentials through the Kuven UI. The existing `CloudProvider` model (with `organization_id` and encrypted `api_token`) continues to serve this purpose. Kuven uses these credentials when generating CAPI manifests — they are injected as Kubernetes Secrets in the tenant's namespace on the management cluster.

Kuven never provisions resources on its own cloud accounts for tenants. Kuven's cloud accounts are used exclusively for the management cluster infrastructure.

## Consequences

### Positive

- **Zero Kubernetes knowledge required** — tenants interact with a web UI, not Kubernetes APIs
- **Clear billing model** — Kuven charges for the platform, cloud costs are on the tenant's account
- **Reduced support surface** — tenants can't misconfigure CAPI resources or management cluster state
- **Security boundary** — tenants have no access to the management cluster or other tenants' resources

### Negative

- **Less flexibility** — power users who want custom CAPI configurations or direct management cluster access cannot get it
- **Trust requirement** — tenants must trust Kuven with their cloud provider API tokens
- **Support burden** — Kuven is fully responsible for cluster health; failures are Kuven's problem, not the tenant's

### Risks

- **Credential security** — tenant API tokens stored in Kuven's database and as Kubernetes Secrets on the management cluster are high-value targets. Mitigation: encryption at rest, RBAC-scoped access (ADR-0008), audit logging.
- **Blast radius** — a bug in Kuven's manifest generation could affect all tenants on a management cluster. Mitigation: staged rollouts, canary deployments for manifest changes.
- **Vendor lock-in perception** — tenants may resist giving full operational control to Kuven. Mitigation: tenants own their cloud accounts and can always access their infrastructure directly.

## Compliance

- Kuven must never provision resources on the tenant's cloud account outside of what the tenant explicitly requested
- Tenant API tokens must be encrypted at rest in both the database and Kubernetes Secrets
- All operations on a tenant's behalf must be logged and auditable
- The UI must clearly show which cloud account resources are provisioned in

## Notes

### Alternatives Considered

| Decision | Alternative | Reason rejected |
|----------|-----------|----------------|
| Managed PaaS | Self-service toolkit (expose CAPI) | Increases support surface; requires tenants to understand Kubernetes; defeats the purpose of an IDP |
| Tenant-owned credentials | Kuven-owned cloud accounts | Kuven would eat infrastructure costs; complicates billing; tenants lose direct cloud access |
