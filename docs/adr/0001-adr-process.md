---
adr:
  number: 1
  status: proposed
  date: 2026-03-28
  authors: [Francisco Barrento]
  tags: [process, documentation, architecture]
  related: []
---

# Establish ADR Process for Architectural Decisions

## Context

As the project grows, we need a systematic way to capture significant architectural decisions. Without documentation:

- Decisions are forgotten or misunderstood over time
- New team members lack context for why things are built a certain way
- The same debates happen repeatedly
- It's unclear whether a decision was intentional or accidental

We need a lightweight but structured process for recording architectural decisions that is:
- Easy to maintain
- Integrated with our GitHub workflow
- Searchable and referenceable
- Consistent in format

## Decision

We will use Architecture Decision Records (ADRs) to document significant architectural decisions. The process is as follows:

### Storage
- ADRs are stored in `/docs/adr/`
- Files are named `{number}-{slug}.md` (e.g., `0001-adr-process.md`)
- Numbers are sequential, zero-padded to 4 digits

### Format
- Markdown with YAML frontmatter containing metadata
- Template located at `docs/adr/template.md`
- Required sections: Context, Decision, Consequences

### Status Lifecycle
ADRs progress through these statuses:
1. `proposed` — Initial draft
2. `under-review` — PR opened for discussion
3. `accepted` — PR merged, decision stands
4. `implemented` — Decision is in production
5. `deprecated` — Being phased out
6. `superseded` — Replaced by newer ADR
7. `rejected` — Decision not approved

### Review Process
- Each ADR is submitted as a PR
- Requires at least one approval before merging
- Discussion happens in PR comments
- Status is updated after merge

### Numbering
- Sequential 4-digit numbering (0001, 0002, 0003...)
- Numbers are never reused, even for rejected ADRs

## Consequences

### Positive

- **Institutional memory**: Decisions are preserved even as team changes
- **Onboarding**: New developers can understand the "why" behind the architecture
- **Accountability**: Decisions require review and approval
- **Reference**: Easy to cite decisions in code and discussions
- **Evolution tracking**: Can see how architecture evolved over time

### Negative

- **Overhead**: Requires writing and reviewing documents
- **Process friction**: May slow down rapid prototyping
- **Maintenance**: README index must be kept updated
- **Potential for over-documentation**: Risk of creating ADRs for trivial decisions

### Risks

- ADRs may become outdated if not maintained
- Team may skip the process for "quick" decisions
- Documents may be too verbose or too vague

**Mitigation**: Keep the template lightweight, enforce via PR process, and revisit this ADR if the process becomes burdensome.

## Compliance

- PRs touching `/docs/adr/` should follow the PR template
- Significant architectural changes should reference an ADR number
- Code may include `@see ADR-{number}` PHPDoc tags where relevant
- Team leads will ensure ADR process is followed during code review

## Notes

### Alternatives Considered

1. **Wiki-based documentation**: Rejected because wikis lack version control and audit trail
2. **Inline code comments**: Rejected because comments are too localized and hard to discover
3. **No documentation**: Rejected because institutional memory is critical for project longevity
4. **External documentation tool**: Rejected because we want docs co-located with code

### References

- [Michael Nygard's ADR format](https://www.cognitect.com/blog/2011/11/15/documenting-architecture-decisions)
- [MADR (Markdown ADR) specification](https://adr.github.io/madr/)
