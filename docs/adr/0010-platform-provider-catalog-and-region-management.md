---
adr:
  number: 10
  status: proposed
  date: 2026-04-05
  authors: [Francisco Barrento]
  tags: [architecture, cloud-provider, regions, catalog, multi-provider]
  related: [ADR-0005, ADR-0006, ADR-0009]
---

# Platform Provider Catalog and Region Management

## Context

Kuven supports multiple cloud providers (Hetzner, DigitalOcean, AWS, Vultr, Akamai) and a local Docker provider for development. Each provider offers regions (datacenters) where resources are deployed — servers, clusters, networks, firewalls.

The current architecture has three problems:

1. **No platform-level provider concept.** The `CloudProvider` model is organization-scoped — it represents an organization's API credentials for a provider. There is no model representing "Hetzner as a provider Kuven supports." The `CloudProviderType` enum hardcodes provider types in code, but can't hold platform-level API credentials, activation status, or catalog data.

2. **Regions are duplicated per organization.** The `Region` model belongs to an organization's `CloudProvider`. Hetzner's `fsn1` region is duplicated across every organization that connects a Hetzner account. There is no shared source of truth for provider regions.

3. **No platform-owned credentials for catalog syncing.** To sync region data (and later server types, availability zones), Kuven needs its own API credentials per provider. The current model only holds organization credentials — and borrowing an organization's token for platform operations is a security and trust violation.

Additionally, the `Infrastructure` model — which groups resources (servers, firewalls, networks) under an organization + cloud provider + region — is redundant under CAPI (ADR-0005). CAPI isolates resources per workload cluster. The Infrastructure model's 17-step provisioning state machine is superseded. Cross-cluster communication (e.g., a shared observability cluster) is a network connectivity concern (WireGuard mesh), not a shared-resource concern.

## Decision

### Two-tier provider architecture

Introduce a **Provider** model at the platform level and rename the existing `CloudProvider` to **CloudAccount** at the organization level.

- **Provider** (platform-level): Represents a cloud provider Kuven supports. Managed by platform admins. Holds Kuven's own API credentials for catalog syncing. Owns the region catalog. Fields: name, slug (unique), api_token (encrypted), is_active.

- **CloudAccount** (organization-level, renamed from CloudProvider): Represents an organization's credentials for a provider. References a Provider via foreign key (replacing the `CloudProviderType` enum). Fields: name, provider_id (FK), api_token (encrypted), is_verified.

The foreign key from CloudAccount to Provider enforces that organizations can only connect accounts for providers the platform has configured and activated. The `CloudProviderType` enum is removed — the Provider model replaces it.

### Platform-level region catalog

The **PlatformRegion** model replaces the current organization-scoped `Region` model. PlatformRegion belongs to Provider (not to an organization's CloudAccount). Fields: provider_id (FK), name, slug, country, city, is_available, metadata (JSON). Unique constraint on [provider_id, slug].

Regions are synced from provider APIs using Kuven's platform-level credentials via a **RegionSyncService** contract with provider-specific implementations (starting with Hetzner). Sync triggers:
- On provider activation (is_active toggled to true with an API token set)
- Weekly scheduled job
- Manual trigger from admin UI

The catalog pattern is designed to extend to server types and availability zones in the future — same contract, same sync infrastructure, different API endpoints.

### Supported providers

Six providers are supported, defined by a `ProviderSlug` string enum: hetzner, digital_ocean, aws, vultr, akamai, docker. The Docker provider is for local development with a static "local" region — no API sync needed.

### Infrastructure model removal

The `Infrastructure` model is removed. Under CAPI, each workload cluster gets its own isolated set of cloud resources (VMs, networks, firewalls). Resources belong to `KubernetesCluster` directly, with each resource carrying its own region reference. Cross-cluster connectivity is handled via WireGuard overlay, not shared infrastructure.

`KubernetesCluster` gains `cloud_account_id` and `region_id` foreign keys. `Server` keeps its own `region_id` — servers are concrete resources in a specific location, independent of their cluster's primary region (supports edge nodes, multi-region HA).

### ManagementCluster references

`ManagementCluster` gains `provider_id` and `platform_region_id` foreign keys, replacing the current plain string `provider` and `region` columns. All providers including Docker have a Provider record. All regions including Docker's "local" have a PlatformRegion record. No special cases.

### Phased rollout

- **Phase 1**: Provider model, PlatformRegion model, admin UI, Hetzner region sync, ManagementCluster FKs
- **Phase 2**: Rename CloudProvider → CloudAccount, add provider_id FK, drop CloudProviderType enum, org settings becomes "Provider Accounts"
- **Phase 3**: Remove Infrastructure model, resources belong to KubernetesCluster directly

## Consequences

### Positive

- **Single source of truth for regions** — no duplication across organizations, consistent catalog platform-wide
- **Platform-owned credentials** — Kuven can sync catalog data independently of any organization's tokens
- **Provider activation control** — platform admins control which providers are available; organizations can only connect accounts for active providers
- **Extensible catalog** — server types and availability zones follow the same pattern (model, sync service, scheduled job)
- **Simpler mental model** — organizations connect an "account," they don't define a "provider." Regions come from the platform catalog, not per-org configuration
- **Clean resource ownership** — resources belong to their cluster, not an abstract Infrastructure grouping. Matches CAPI's per-cluster isolation model
- **No special cases** — Docker and production providers follow identical patterns (Provider + PlatformRegion)

### Negative

- **Migration effort** — three-phase rollout touches models, migrations, controllers, tests, and frontend across the stack
- **Temporary naming** — `platform_regions` table and `PlatformRegion` model are awkward names needed to avoid collision with the existing `regions` table until Phase 2 resolves it
- **CloudManager interaction** — ADR-0009's CloudManager resolves drivers by provider type. The driver resolution must adapt from the `CloudProviderType` enum to the Provider model's slug. This is a minor change but touches the Manager pattern
- **Seeder vs production gap** — local dev uses seeders to create providers; production uses the admin UI. The same data, two different creation paths

### Risks

- **Provider API differences** — each provider's region API returns different fields and structures. Mitigation: the `metadata` JSON column captures provider-specific extras without modeling every field; the `RegionSyncService` contract normalizes the output
- **Stale catalog data** — if the weekly sync fails silently, the region catalog becomes outdated. Mitigation: log sync results, surface last-synced timestamp in admin UI, alert on sync failures
- **Phase 2/3 scope creep** — the CloudAccount rename and Infrastructure removal touch many files. Mitigation: each phase is an independent PR with its own tests; phases can be shipped weeks apart

## Compliance

- All new models must follow existing conventions: `final` class, `declare(strict_types=1)`, `HasUuids`, `HasFactory`, PHPDoc `@property-read` blocks
- Provider policy must gate all actions to platform admins (`$user->platform_role === PlatformRole::Admin`)
- RegionSyncService implementations must be idempotent — running sync multiple times produces no duplicates (use `updateOrCreate` on [provider_id, slug])
- Migrations must not include `down()` methods (existing convention)
- Feature tests must cover admin access, non-admin 403, guest redirect for all endpoints
- The `ProviderSlug` enum is the single source of truth for supported provider slugs — used for validation, driver resolution, and UI display

## Notes

### Alternatives Considered

| Decision | Alternative | Reason rejected |
|----------|-----------|----------------|
| Platform-level Provider model | Keep CloudProviderType enum | Enum can't hold API credentials, activation status, or own the region catalog |
| Platform-level regions | Per-organization regions | Duplicates data, can't use platform credentials for syncing |
| Provider + CloudAccount naming | Provider + CloudProvider | "CloudProvider" implies the provider itself, not an organization's account credentials; confusing alongside Provider |
| Foreign key from CloudAccount to Provider | Keep CloudProviderType enum on CloudAccount | Loose coupling; no referential integrity; can't enforce "only connect to active providers" |
| Remove Infrastructure model | Keep Infrastructure as optional grouping | CAPI isolates resources per cluster; Infrastructure adds a layer with no value under the new model |
| Server owns its own region_id | Server inherits region from cluster | Servers are concrete resources in specific locations; multi-region clusters and edge nodes need per-server region tracking |
| Regions synced from APIs | Static region definitions in code | Requires manual updates when providers add datacenters; sync keeps catalog current automatically |
