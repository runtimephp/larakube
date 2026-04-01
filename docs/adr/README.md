# Architecture Decision Records (ADRs)

This directory contains Architecture Decision Records (ADRs) for this project. ADRs are short documents that capture significant architectural decisions, their context, and consequences.

## What is an ADR?

An ADR is a design document that provides:
- **Context**: The problem being solved and constraints
- **Decision**: The chosen solution
- **Consequences**: Trade-offs, benefits, and risks

ADRs are immutable—once created, they are never edited. If a decision changes, a new ADR is created that references (and supersedes) the old one.

## Status Legend

| Status | Description |
|--------|-------------|
| `proposed` | Initial draft, not yet reviewed |
| `under-review` | Open PR, discussion in progress |
| `accepted` | Approved and ready for implementation |
| `implemented` | Decision is in production |
| `deprecated` | Still in use, but being phased out |
| `superseded` | Replaced by a newer ADR |
| `rejected` | Decision was not approved |

## How to Create an ADR

1. **Copy the template**: `cp docs/adr/template.md docs/adr/{number}-{slug}.md`
2. **Fill in the frontmatter**:
   - `number`: Next sequential number (e.g., 0001, 0002)
   - `date`: Today's date (YYYY-MM-DD)
   - `authors`: Your name(s)
   - `tags`: Relevant topics (e.g., database, caching, api)
   - `related`: Links to related ADRs (e.g., [ADR-0001](0001-adr-process.md))
3. **Complete the sections**: Context, Decision, Consequences
4. **Create a PR**: Submit for team review
5. **Update status**: After merge, update status to `accepted`

## ADR Index

<!-- AUTO-GENERATED TOC: Update when adding new ADRs -->

| ADR | Title | Status | Date |
|-----|-------|--------|------|
| [ADR-0001](0001-adr-process.md) | Establish ADR Process | proposed | 2026-03-28 |
| [ADR-0002](0002-architecture-overview.md) | Architecture Overview | proposed | 2026-03-28 |
| [ADR-0003](0003-multipass-local-cloud-provider.md) | Multipass as Local Cloud Provider | proposed | 2026-03-28 |
| [ADR-0004](0004-kubernetes-architecture-and-roadmap.md) | Kubernetes Architecture, Provisioning Pipeline, and Roadmap | superseded | 2026-03-29 |
| [ADR-0005](0005-cluster-api-as-cluster-lifecycle-engine.md) | Adopt Cluster API as Cluster Lifecycle Engine | proposed | 2026-04-01 |
| [ADR-0006](0006-managed-paas-tenant-responsibility-model.md) | Kuven as Managed PaaS — Tenant Responsibility Model | proposed | 2026-04-01 |
| [ADR-0007](0007-management-cluster-bootstrap-strategy.md) | Management Cluster Bootstrap Strategy | proposed | 2026-04-01 |
| [ADR-0008](0008-tenant-isolation-via-rbac-scoped-namespaces.md) | Tenant Isolation via RBAC-Scoped Namespaces | proposed | 2026-04-01 |
| [ADR-0009](0009-cloud-provider-driver-pattern.md) | Cloud Provider Driver Pattern | proposed | 2026-04-01 |

## Related Documentation

- See `/docs/` for broader architecture documentation
- ADRs are referenced in code via `@see ADR-{number}` PHPDoc tags

## Tools

- **Template**: [`template.md`](template.md) — Copy this for new ADRs
- **This README**: Explains the process and maintains the index
