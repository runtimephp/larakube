---
adr:
  number: 7
  status: proposed
  date: 2026-04-01
  authors: [Francisco Barrento]
  tags: [kubernetes, capi, management-cluster, bootstrap, operations]
  related: [ADR-0005, ADR-0006, ADR-0008]
---

# Management Cluster Bootstrap Strategy

## Context

CAPI requires a management cluster — a Kubernetes cluster that runs the CAPI controllers, infrastructure providers, and reconciliation loops. All tenant workload clusters are orchestrated from this management cluster.

Key constraints:

- **The management cluster is Kuven's infrastructure**, not a tenant's (ADR-0006). Tenants never know it exists.
- **Kuven is deployed on Laravel Cloud**, which does not allow installing arbitrary binaries (`clusterctl`, `kind`, `kubectl`) in its containers.
- **Bootstrap is rare** — one management cluster per region, provisioned once and operated for months or years.
- **Ongoing operations** (applying CAPI manifests, polling status) are pure HTTP calls to the Kubernetes API, which run on standard Laravel Cloud queue workers with no binary dependencies.

## Decision

### Management Cluster Topology

Kuven operates a **shared management cluster per region**. All tenants in a region share the same management cluster, isolated by Kubernetes namespace (ADR-0008).

Starting configuration:

| Component | Specification |
|-----------|--------------|
| Control plane | 3 nodes (HA) |
| Provider | Kuven's own cloud account (Hetzner initially) |
| CAPI providers installed | CAPH (Hetzner), additional providers as needed |
| Expected scale | One management cluster serves all tenants in a region |

Additional management clusters are added per-region as Kuven expands geographically.

### Bootstrap via Local CLI

The management cluster is bootstrapped by a Kuven operator running the `kuven init` Artisan command on a machine with the required tools installed:

```
kuven init --provider=hetzner --region=nuremberg
```

The command wraps the standard CAPI bootstrap flow:

1. `kind create cluster` — creates a temporary local bootstrap cluster
2. `clusterctl init --infrastructure hetzner` — installs CAPI controllers on the bootstrap cluster
3. Apply Cluster manifest — CAPI provisions the real management cluster on Kuven's cloud account
4. Wait for the management cluster to be ready
5. `clusterctl move` — pivots CAPI state from the bootstrap cluster to the real management cluster
6. Store the management cluster kubeconfig encrypted in Kuven's database
7. `kind delete cluster` — destroys the temporary bootstrap cluster

**Prerequisites on the operator's machine**: `clusterctl`, `kind`, `kubectl`, Docker.

### Kubeconfig Storage

The management cluster kubeconfig is stored encrypted in Kuven's database. Laravel Cloud queue workers retrieve it at runtime to make Kubernetes API calls. The kubeconfig is never exposed to tenants.

### Management Cluster Lifecycle

| Operation | How |
|-----------|-----|
| Bootstrap | `kuven init` (local CLI, one-time) |
| CAPI provider upgrades | `clusterctl upgrade` (operator runs manually or via CI) |
| Kubernetes upgrades | Standard CAPI self-managed upgrade flow |
| Monitoring | Kuven health check job polls management cluster API |
| Scaling | Add/remove nodes via CAPI MachineDeployment on the management cluster itself |

## Consequences

### Positive

- **Simple bootstrap** — a single CLI command wraps the entire flow. No automated container orchestration needed for a rare operation.
- **No Laravel Cloud constraints** — bootstrap runs locally where all tools are available. Ongoing operations are HTTP-only.
- **Shared efficiency** — one management cluster per region avoids per-tenant control plane overhead.
- **Standard CAPI flow** — `clusterctl init` + `clusterctl move` is the documented bootstrap pattern. Nothing custom.

### Negative

- **Manual operation** — bootstrap requires an operator with local tooling. Not self-service.
- **Single management cluster per region** — if it goes down, all tenant operations in that region are blocked.
- **Kubeconfig in database** — the management cluster credential is a high-value secret. Compromise gives access to all tenant CAPI resources.

### Risks

- **Management cluster failure** — loss of the management cluster blocks all provisioning and status polling. Mitigation: HA control plane (3 nodes), etcd backups, monitoring with alerting.
- **Kubeconfig rotation** — the stored kubeconfig may expire or need rotation. Mitigation: use service account tokens with longer TTLs, or implement automatic rotation.
- **CAPI scalability** — a single management cluster may hit limits with very large tenant counts. Mitigation: monitor CAPI controller resource usage, scale vertically before adding management clusters.

## Compliance

- The management cluster kubeconfig must be encrypted at rest in the database
- `kuven init` must validate prerequisites (`clusterctl`, `kind`, `kubectl`, Docker) before starting
- Management cluster health must be monitored via a scheduled Laravel job
- Bootstrap procedures must be documented in a runbook alongside this ADR

## Notes

### Alternatives Considered

| Decision | Alternative | Reason rejected |
|----------|-----------|----------------|
| Local CLI bootstrap | Automated bootstrap container (Docker/Fly.io/Fargate) | Over-engineering for a 1-3 time operation per region |
| Shared management cluster | Per-tenant management cluster | Unnecessary overhead — tenants never see the management cluster. Adds cost and complexity with no user-facing benefit |
| Kuven-owned cloud account | Management cluster on tenant infrastructure | Management cluster must be independent of any single tenant's billing, quotas, or account status |

### Future Considerations

- Automated bootstrap via CI/CD pipeline (GitHub Actions) if the number of regions grows significantly
- Per-tenant management clusters for enterprise compliance requirements (hard isolation)
- Multi-region management cluster federation
