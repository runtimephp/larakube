---
adr:
    number: 3
    status: proposed
    date: 2026-03-28
    authors: [Francisco Barrento]
    tags: [infrastructure, local-development, cloud-provider, multipass]
    related: [ADR-0002]
---

# Multipass as Local Cloud Provider for Development

## Context

LaraKube currently supports Hetzner and DigitalOcean as cloud providers. Both require a paid account,
live API credentials, and an internet connection to provision any server — making local development
and testing slower and more expensive than it needs to be.

Developers need a way to:

- Test the full server provisioning flow without spending money or hitting real cloud APIs
- Work offline or in environments where cloud access is restricted
- Spin up and tear down VMs quickly during feature development

A local VM provider that slots into the existing `CloudProviderType` / `CloudProviderFactory` /
`ServerService` architecture would solve this without adding any new abstractions.

### Options Considered

| Option        | Description                                                                      |
| ------------- | -------------------------------------------------------------------------------- |
| **Multipass** | Canonical's CLI tool for launching Ubuntu VMs via HyperKit (Mac) or KVM (Linux)  |
| **Vagrant**   | VM lifecycle manager using Vagrantfiles; supports multiple hypervisors and boxes |
| **Lima**      | Lightweight Linux VMs on macOS with automatic file sharing and port forwarding   |
| **Docker**    | Container runtime — not actual VMs, but can simulate server environments         |
| **QEMU/KVM**  | Raw hypervisor with full hardware emulation; maximum control on Linux            |

## Decision

We will add **Multipass** as a first-class `CloudProviderType` (`multipass`) in LaraKube.

It will implement the same `CloudProviderService` and `ServerService` contracts as Hetzner and
DigitalOcean, making it transparent to the rest of the application. The `cloud-provider:add`,
`server:create`, `server:list`, and `server:delete` commands will work identically regardless of
whether the provider is Multipass or a real cloud.

Multipass is explicitly a **local development provider only** — it is not intended for production use.

### Technical Decisions

**Factory token parameter** — The `$token` parameter on `CloudProviderFactory::makeServerService()`
and `makeForValidation()` becomes nullable (`?string $token = null`). The `api_token` database column
becomes nullable via migration. `CreateCloudProviderData::$apiToken` becomes `?string`. The
`AddCloudProviderCommand` skips the token prompt when Multipass is selected.

**MultipassService validation** — `validateToken()` runs `multipass version` via Symfony Process.
Returns `true` if exit code is 0 (binary exists and is functional). No actual token validation — the
"validation" confirms the CLI tool is available on the system.

**MultipassServerService CLI mapping**:

| Operation   | Command                                                                           |
| ----------- | --------------------------------------------------------------------------------- |
| `getAll()`  | `multipass list --format json`                                                    |
| `create()`  | `multipass launch <image> --name <name> --cpus <n> --memory <size> --disk <size>` |
| `find()`    | `multipass info <name> --format json`                                             |
| `destroy()` | `multipass delete <name>` then `multipass purge`                                  |

**CreateServerData expansion** — Add nullable fields for Multipass resource specs: `?int $cpus`,
`?string $memory` (e.g., `"2G"`), `?string $disk` (e.g., `"20G"`). Hetzner/DigitalOcean ignore
these fields. Multipass uses them with sensible defaults when null.

**VM naming** — Users provide a base name (e.g., `web-1`), and `MultipassServerService` appends a
short unique suffix (e.g., `web-1-a3f7b2`). The full generated name is used as the external ID since
Multipass identifies VMs by name (enforces uniqueness).

**Status mapping** — `ServerStatus::fromMultipass(string $status)`:

| Multipass            | ServerStatus |
| -------------------- | ------------ |
| Running              | Running      |
| Stopped, Suspended   | Off          |
| Starting, Restarting | Starting     |
| Other                | Unknown      |

**Delete behavior** — `multipass delete <name>` followed by `multipass purge`. Purge affects all
deleted VMs, but in LaraKube's context a delete should be permanent, matching Hetzner/DigitalOcean
behavior.

**Command adjustments** — `CreateServerCommand` checks the selected provider's type and shows
different prompts:

- **Hetzner/DigitalOcean**: name, type, image, region (existing behavior)
- **Multipass**: name, image (Ubuntu release), cpus, memory, disk. Type set to `"custom"`, region
  to `"local"` automatically

**Testing strategy** — InMemory test doubles (`InMemoryMultipassService`,
`InMemoryMultipassServerService`, `InMemoryMultipassFactory`) replace the entire service in tests,
mirroring the Hetzner pattern exactly. Tests never require Multipass to be installed. No Process
abstraction layer needed.

## Consequences

### Positive

- **Zero cost** — no cloud account or API token required to develop and test the provisioning flow
- **Offline capable** — works without internet access once Multipass is installed
- **Fast** — VM boot time is seconds, not minutes
- **Real VMs** — unlike Docker, these are full Ubuntu VMs with real networking and SSH access, making
  the simulation accurate
- **No new abstractions** — fits the existing `CloudProviderService` / `ServerService` pattern without
  any structural changes to the codebase
- **InMemory test doubles** — follows the same testing strategy as Hetzner and DigitalOcean; tests
  never require Multipass to be installed

### Negative

- **Mac and Linux only** — Multipass has limited Windows support; this is acceptable given the team's OS environment
- **Nullable `api_token`** — the `cloud_providers.api_token` column must be made nullable to
  accommodate Multipass, which has no token concept. This is a schema change that affects all providers.
- **DTO expansion** — `CreateServerData` gains Multipass-specific nullable fields that other
  providers ignore
- **Local-only** — Multipass VMs exist on the machine running LaraKube. If LaraKube runs on a remote
  server, the Multipass provider is not useful. This is by design and should be documented.
- **Binary dependency** — Multipass must be installed separately. LaraKube cannot install it and
  should fail gracefully with a clear error if it is missing.
- **Purge side effect** — `multipass purge` affects all deleted VMs, not just the targeted one

### Risks

- **Process coupling** — the implementation shells out to the `multipass` CLI. If Multipass changes
  its CLI interface or JSON output format, the integration breaks. Mitigation: use Symfony Process;
  InMemory test doubles ensure tests never depend on the binary.
- **Multipass availability** — Multipass is maintained by Canonical and is actively developed, but it
  is not as ubiquitous as Docker. Developers who have never used it will need to install it.
- **Cross-platform differences** — Multipass behavior may vary between macOS and Linux.

## Compliance

- `MultipassService` and `MultipassServerService` must implement the existing contracts —
  no interface changes are permitted
- All process execution must use **Symfony Process** — `exec()` and `shell_exec()` are prohibited
- InMemory test doubles are required, following the pattern in `App\Services\InMemory\`
- The Multipass provider must be clearly labelled `"Multipass (Local)"` in the UI and CLI to
  distinguish it from real cloud providers
- `composer test` must pass with 100% coverage after implementation

## Notes

### Why Multipass over the alternatives

**Vagrant** was the strongest alternative. It is more flexible (supports many hypervisors and OS
images) and has broader community adoption. However, it requires a `Vagrantfile` per environment and
a heavier setup process. Multipass is purpose-built for quickly spinning up Ubuntu VMs with a single
command, which maps more directly to what LaraKube does — `server:create` → one VM, done.

**Lima** is a good option for Mac users specifically, but lacks first-class Linux support and has
less straightforward cloud-init integration. Multipass works identically on Mac and Linux.

**Docker** was ruled out because containers are not VMs. The provisioning flow in LaraKube (SSH keys,
IP addresses, real networking) is meaningfully different in a container context and would require
special-casing throughout the codebase.

**QEMU/KVM** is too low-level. It has no CLI wrapper suitable for programmatic VM lifecycle
management without significant additional tooling.

### Deferred to Future Issues

- SSH key injection via cloud-init when creating Multipass VMs
- Multipass network configuration for multi-VM clusters

### Post-ADR-0005 Scope Clarification (2026-04-01)

Multipass is scoped to **local workload clusters only** — for developer testing of application deployments on a real Kubernetes cluster. It is NOT used for bootstrapping the CAPI management cluster; `clusterctl init` with `kind` serves that role (ADR-0007). The Multipass driver will be narrowed to read-only inventory queries under the CloudManager pattern (ADR-0009).
