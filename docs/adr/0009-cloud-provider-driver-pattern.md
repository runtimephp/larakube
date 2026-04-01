---
adr:
  number: 9
  status: proposed
  date: 2026-04-01
  authors: [Francisco Barrento]
  tags: [architecture, cloud-provider, driver-pattern, extensibility]
  related: [ADR-0003, ADR-0005]
---

# Cloud Provider Driver Pattern

## Context

With the adoption of CAPI (ADR-0005), provider service contracts lose their write methods — CAPI infrastructure providers handle resource creation and lifecycle. The remaining value is **read-only inventory queries**: listing servers, networks, firewalls, and SSH keys from a tenant's cloud account for dashboard visibility.

The current architecture uses a `CloudProviderFactory` with explicit `make*()` methods for each service:

```php
$factory->makeServerService($token);
$factory->makeSshKeyService($token);
$factory->makeNetworkService($token);
$factory->makeFirewallService($token);
$factory->makeNatGatewayService($token);
```

This has scaling problems:

- Adding a new resource type requires adding a new `make*()` method to the factory and every provider implementation
- Adding a new provider requires implementing 6+ service interfaces
- The factory knows about every service type — it's a god object
- Third-party providers cannot be added without modifying the factory

Laravel solves this pattern with Manager classes (DatabaseManager, CacheManager, SocialiteManager). Socialite specifically demonstrates how to support first-party drivers that ship with the package and third-party drivers installable as separate Composer packages.

## Decision

Replace `CloudProviderFactory` with a `CloudManager` that follows Laravel's Manager pattern.

### Driver Interface

```php
interface CloudDriver
{
    public function servers(): ServerQuery;
    public function networks(): NetworkQuery;
    public function firewalls(): FirewallQuery;
    public function sshKeys(): SshKeyQuery;
}
```

Each query interface exposes read-only methods only:

```php
interface ServerQuery
{
    public function list(): Collection;
    public function find(string $externalId): ?ServerData;
}
```

### Manager Usage

```php
// Resolve by provider type
app(CloudManager::class)->driver('hetzner')->servers()->list();

// Resolve from a CloudProvider model
app(CloudManager::class)->for($cloudProvider)->networks()->list();
```

The `for()` method reads the provider's type and injects the encrypted API token automatically.

### Driver Registration

First-party drivers (Hetzner, DigitalOcean, Multipass) are registered in the `CloudManager`:

```php
class CloudManager extends Manager
{
    public function createHetznerDriver(): CloudDriver { ... }
    public function createDigitalOceanDriver(): CloudDriver { ... }
    public function createMultipassDriver(): CloudDriver { ... }
}
```

Third-party drivers are installable as separate Composer packages and registered via `CloudManager::extend()`:

```php
// In a service provider from a third-party package
CloudManager::extend('aws', function ($app, $config) {
    return new AwsCloudDriver($config['token']);
});
```

This follows the exact pattern Socialite uses for third-party providers (`socialite-providers/providers`).

### Token Validation

The existing `CloudProviderService::validateToken()` method moves into the driver:

```php
interface CloudDriver
{
    public function validateToken(): bool;
    public function servers(): ServerQuery;
    // ...
}
```

### Migration Path

1. Introduce `CloudManager` and `CloudDriver` interface
2. Migrate existing Hetzner, DigitalOcean, and Multipass service implementations to drivers
3. Remove `CloudProviderFactory` and individual `make*()` methods
4. Remove write methods from all service classes
5. Update all call sites to use `CloudManager`

## Consequences

### Positive

- **Laravel-native pattern** — developers already understand Manager/Driver from Cache, Queue, Mail, Filesystem, Socialite
- **Extensible** — third-party providers are installable packages, no core code changes required
- **Simpler interface** — one `CloudDriver` interface instead of 6 separate service contracts
- **Read-only by design** — the new interfaces only expose query methods, preventing accidental writes
- **Token injection** — the `for($cloudProvider)` method handles credential injection, eliminating token-passing boilerplate

### Negative

- **Migration effort** — existing factory and service implementations must be refactored
- **Breaking change** — all call sites using `CloudProviderFactory` must be updated
- **Third-party driver quality** — community-maintained drivers may lag behind API changes

### Risks

- **Driver interface stability** — changing the `CloudDriver` interface breaks all third-party drivers. Mitigation: design the interface carefully before publishing; version it.
- **Provider API differences** — not all providers expose the same resource types. Mitigation: query interfaces return empty collections for unsupported resources rather than throwing.

## Compliance

- All drivers must implement the full `CloudDriver` interface
- InMemory test doubles must be provided for first-party drivers
- Third-party driver packages must follow a documented naming convention (e.g., `kuven/cloud-driver-aws`)
- The `CloudDriver` interface must not include write methods — resource lifecycle is CAPI's responsibility (ADR-0005)

## Notes

### Alternatives Considered

| Decision | Alternative | Reason rejected |
|----------|-----------|----------------|
| Manager/Driver pattern | Keep CloudProviderFactory | Factory doesn't support third-party extensions; grows with every new service type |
| Manager/Driver pattern | Strategy pattern (no Manager) | Loses Laravel's built-in Manager infrastructure (config resolution, driver caching, `extend()`) |
| Separate Composer packages | Monorepo drivers | Socialite model is well-understood; separate packages allow independent versioning |
