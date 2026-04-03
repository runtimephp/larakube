# Contributing to Kuven

Welcome! This guide will get you from zero to your first pull request.

## What is Kuven?

Kuven is a **managed PaaS for Kubernetes** built with Laravel. Organizations connect their cloud providers (Hetzner, DigitalOcean, etc.), and Kuven handles the full lifecycle: provisioning clusters via [Cluster API](https://cluster-api.sigs.k8s.io/), deploying applications, managing secrets, networking, and observability.

The goal is to let developers deploy with a single PR — Vercel/Laravel Cloud style — without touching Kubernetes directly. DevOps teams configure the infrastructure once; developers just ship.

## Architecture at a Glance

Kuven follows a **server-driven SPA** pattern: Laravel handles routing, auth, and business logic; React + Inertia.js renders the UI without a separate API layer.

```
Browser (React 19 + Inertia.js v3 + Tailwind CSS v4)
    |
Laravel 13 (controllers, actions, jobs)
    |
    ├── Kubernetes API (via Saloon v4, typed manifests)
    ├── Cloud Provider APIs (Hetzner, DigitalOcean — driver pattern)
    └── PostgreSQL (tenants, infrastructure state, credentials)
```

**Key architectural decisions** are documented as ADRs in [`docs/adr/`](docs/adr/). These are the source of truth for why things are built the way they are. Start with:

- [ADR-0002: Architecture Overview](docs/adr/adr-0002-architecture-overview.md) — tech stack and patterns
- [ADR-0005: Cluster API as Cluster Lifecycle Engine](docs/adr/adr-0005-cluster-api-as-cluster-lifecycle-engine.md) — the core provisioning strategy
- [ADR-0006: Managed PaaS Tenant Responsibility Model](docs/adr/adr-0006-managed-paas-tenant-responsibility-model.md) — what Kuven owns vs what tenants own
- [ADR-0008: Tenant Isolation via RBAC-Scoped Namespaces](docs/adr/adr-0008-tenant-isolation-via-rbac-scoped-namespaces.md) — multi-tenancy approach

## Getting Started

### Prerequisites

- PHP 8.4+
- Node.js 22+
- Composer
- SQLite (local dev) or PostgreSQL

### Setup

```bash
git clone https://github.com/getkuven/kuven.git
cd kuven

composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate

npm run build
```

### Run the app

```bash
composer run dev
```

This starts the web server, queue worker, log tail, and Vite dev server.

### Run the test suite

```bash
composer test
```

This runs everything: Pest tests (100% coverage required), PHPStan, Rector, Pint, ESLint, and TypeScript checks. **This must pass before you open a PR.**

## How We Work

### Pick an issue

Browse [open issues](https://github.com/getkuven/kuven/issues) and comment on one you'd like to work on. Issues labeled `good first issue` are ideal for new contributors.

### Branch and PR workflow

1. Create a branch from `main` (this is the production branch)
2. Make your changes with tests
3. Run `composer test` — everything must pass
4. Push and open a PR against `main`
5. CodeRabbit reviews automatically — address its comments
6. Francisco reviews and merges

### Code conventions

- **Follow existing patterns.** When creating or editing a file, check sibling files for structure, naming, and approach.
- **Descriptive names.** `isRegisteredForDiscounts`, not `discount()`.
- **PHP 8.4 features.** Constructor promotion, `final readonly class`, backed enums, `#[SensitiveParameter]` where appropriate.
- **No new dependencies** without approval.
- **No new root directories** without approval.

### Testing rules

Every change must be tested. See [`tests/CONTRIBUTING.md`](tests/CONTRIBUTING.md) for the full testing guide. The highlights:

- Use `test()` syntax (not `it()`)
- Imperative mood: `test('creates a namespace')`, not `test('it should create a namespace')`
- New tests go at the **top** of the file
- Use InMemory test doubles, never Mockery
- Every model needs: creation test, relationship tests, casts test, UUID test, and `toArray()` field order test
- `composer test` enforces 100% code coverage — no exceptions

### Code style

- **PHP**: Run `vendor/bin/pint --dirty` before committing. Pint is the formatter.
- **TypeScript/JS**: ESLint + Prettier handle formatting.
- **Rector**: Automated refactoring rules are enforced. Don't fight them.

## Where to Find Things

| What | Where |
|------|-------|
| Architecture decisions | [`docs/adr/`](docs/adr/) |
| UI design specs | [`docs/design/`](docs/design/) |
| Testing conventions | [`tests/CONTRIBUTING.md`](tests/CONTRIBUTING.md) |
| Kubernetes integration | `app/Http/Integrations/Kubernetes/` |
| Cloud provider drivers | `app/Services/` |
| React pages | `resources/js/pages/` |
| UI components | `resources/js/components/` |
| Artisan commands | `app/Console/Commands/` |
| GitHub issues (roadmap) | [Issues](https://github.com/getkuven/kuven/issues) |

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13 |
| Frontend | React 19, Inertia.js v3, Tailwind CSS v4 |
| Kubernetes | Saloon v4, typed manifests, structured error handling |
| Queue | Laravel Horizon |
| Feature flags | Laravel Pennant |
| Routing | Ziggy, Laravel Wayfinder |
| Testing | Pest 4, PHPStan (Larastan), Rector |
| Auth | Laravel Sanctum |
| Code style | Laravel Pint, ESLint, Prettier |

## Questions?

Open a [discussion](https://github.com/getkuven/kuven/discussions) or comment on the issue you're working on.
