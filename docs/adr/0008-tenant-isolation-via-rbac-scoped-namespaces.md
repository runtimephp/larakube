---
adr:
  number: 8
  status: proposed
  date: 2026-04-01
  authors: [Francisco Barrento]
  tags: [kubernetes, security, multi-tenancy, rbac, isolation]
  related: [ADR-0005, ADR-0006, ADR-0007]
---

# Tenant Isolation via RBAC-Scoped Namespaces

## Context

Kuven operates a shared management cluster per region (ADR-0007). Multiple tenants' CAPI resources (Cluster objects, MachineDeployments, infrastructure provider resources) coexist on the same cluster. Tenant cloud provider credentials (API tokens) are stored as Kubernetes Secrets on the management cluster.

This requires a strong isolation model. A failure in isolation means:

- Tenant A could read tenant B's cloud provider credentials
- Tenant A could modify or delete tenant B's clusters
- A bug in Kuven could accidentally cross-contaminate operations

Kubernetes namespaces alone are not a security boundary — they are an organizational mechanism. Without additional controls, any ServiceAccount with cluster-wide permissions can access all namespaces.

## Decision

Each tenant (Kuven organization) gets a dedicated namespace on the management cluster, with RBAC-enforced isolation.

### Namespace Convention

```
kuven-org-{organization_id}
```

Example: `kuven-org-01HXK5P3M7NW8R9QZFY6D4BEGS`

### Per-Tenant ServiceAccount

When a tenant's organization is created in Kuven, the following resources are provisioned on the management cluster:

1. **Namespace** — `kuven-org-{id}`
2. **ServiceAccount** — `kuven-operator` in the tenant's namespace
3. **Role** — full CAPI resource access scoped to the namespace only
4. **RoleBinding** — binds the ServiceAccount to the Role

```yaml
apiVersion: rbac.authorization.k8s.io/v1
kind: Role
metadata:
  namespace: kuven-org-{id}
  name: kuven-operator
rules:
  - apiGroups: ["cluster.x-k8s.io", "infrastructure.cluster.x-k8s.io", "bootstrap.cluster.x-k8s.io", "controlplane.cluster.x-k8s.io"]
    resources: ["*"]
    verbs: ["*"]
  - apiGroups: [""]
    resources: ["secrets", "configmaps"]
    verbs: ["*"]
```

Kuven stores the ServiceAccount token for each tenant. When operating on a tenant's clusters, Kuven authenticates to the management cluster using the tenant's scoped ServiceAccount — not a cluster-admin credential.

### Credential Storage

Tenant cloud provider API tokens are stored as Kubernetes Secrets in the tenant's namespace. CAPI infrastructure providers read credentials from these Secrets when provisioning workload clusters. The RBAC Role ensures these Secrets are only accessible from within the tenant's namespace.

### NetworkPolicies

Default-deny NetworkPolicies are applied to each tenant namespace to prevent cross-namespace network access on the management cluster:

```yaml
apiVersion: networking.k8s.io/v1
kind: NetworkPolicy
metadata:
  namespace: kuven-org-{id}
  name: default-deny
spec:
  podSelector: {}
  policyTypes: ["Ingress", "Egress"]
```

CAPI controller pods (which run in the `capi-system` namespace) are exempted via targeted policies.

### ResourceQuotas

Each tenant namespace gets a ResourceQuota to prevent a single tenant's CAPI operations from consuming excessive management cluster resources:

```yaml
apiVersion: v1
kind: ResourceQuota
metadata:
  namespace: kuven-org-{id}
  name: tenant-quota
spec:
  hard:
    count/clusters.cluster.x-k8s.io: "10"
    count/machinedeployments.cluster.x-k8s.io: "50"
    count/secrets: "100"
```

Quotas are configurable per tenant (plan-based limits).

### Kuven's Internal Credentials

Kuven maintains two levels of management cluster access:

| Credential | Scope | Used for |
|-----------|-------|----------|
| Cluster-admin kubeconfig | Full cluster access | Namespace/RBAC provisioning, CAPI controller management, management cluster upgrades |
| Tenant ServiceAccount token | Single namespace | All tenant-scoped operations (apply manifests, poll status, manage secrets) |

The cluster-admin kubeconfig is used only by `kuven init` and administrative operations. Day-to-day tenant operations use the scoped ServiceAccount.

## Consequences

### Positive

- **Kubernetes-enforced isolation** — RBAC prevents cross-namespace access at the API server level. A bug in Kuven that accidentally targets the wrong namespace gets a 403, not a data leak.
- **Principle of least privilege** — tenant operations use the minimum required permissions.
- **Auditable** — Kubernetes audit logs capture all API calls per ServiceAccount, providing per-tenant audit trails.
- **Quota protection** — no single tenant can starve the management cluster.

### Negative

- **Provisioning overhead** — every new organization requires creating a namespace, ServiceAccount, Role, RoleBinding, NetworkPolicy, and ResourceQuota on the management cluster.
- **Token management** — ServiceAccount tokens must be stored, rotated, and potentially refreshed.
- **CAPI controller access** — CAPI controllers need cross-namespace access to reconcile resources. This requires careful RBAC configuration for the controller ServiceAccounts.

### Risks

- **RBAC misconfiguration** — an overly permissive Role could grant cross-namespace access. Mitigation: Role definitions are templated and tested; never manually edited.
- **CAPI controller privilege escalation** — CAPI controllers run with broad permissions by default. A compromised controller could access all tenant namespaces. Mitigation: monitor CAPI controller pods, restrict their RBAC to the minimum required, keep CAPI versions updated.
- **Management cluster compromise** — if the management cluster itself is compromised, all tenant credentials are exposed regardless of namespace isolation. Mitigation: HA control plane, network-level access restrictions, regular security audits.

## Compliance

- Tenant namespace provisioning must be automated — triggered on organization creation in Kuven
- RBAC Role definitions must be version-controlled and applied from templates
- ServiceAccount tokens must be encrypted at rest in Kuven's database
- Namespace deletion must cascade — destroying a tenant's namespace cleans up all CAPI resources
- Regular audits of management cluster RBAC configurations

## Notes

### Alternatives Considered

| Decision | Alternative | Reason rejected |
|----------|-----------|----------------|
| Namespace + RBAC | Per-tenant management cluster | Unnecessary cost and complexity — tenants never access the management cluster |
| Namespace + RBAC | vCluster (virtual clusters) | Adds operational complexity; standard RBAC is sufficient when only Kuven accesses the management cluster |
| Scoped ServiceAccounts | Single cluster-admin for all operations | Violates least privilege; a bug could affect any tenant |
