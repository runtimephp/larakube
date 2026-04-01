---
adr:
    number: 2
    status: proposed
    date: 2026-03-28
    authors: [Francisco Barrento]
    tags: [architecture, overview, stack, foundation]
    related: []
---

# Architecture Overview

## Context

This document captures the current architectural foundation of the application. It serves as a baseline for future ADRs and provides context for developers joining the project.

## Decision

The application is built on the following technology stack and architectural patterns:

### Core Stack

| Component | Technology      | Version | Purpose               |
| --------- | --------------- | ------- | --------------------- |
| PHP       | PHP             | 8.4     | Backend runtime       |
| Framework | Laravel         | v12     | Application framework |
| Frontend  | React           | v19     | UI components         |
| Inertia   | Inertia.js      | v2      | Server-driven SPA     |
| Styling   | Tailwind CSS    | v4      | Utility-first CSS     |
| Database  | MySQL           | 8.0+    | Primary data store    |
| Testing   | Pest            | v4      | PHP testing framework |
| Auth      | Laravel Sanctum | v4      | API authentication    |

### Directory Structure

```
larakube/
├── app/                    # Application logic
│   ├── Http/
│   │   ├── Controllers/   # Request handlers
│   │   ├── Middleware/    # Request/response filters
│   │   └── Requests/      # Form request validation
│   ├── Models/            # Eloquent models
│   └── Services/          # Business logic services
├── bootstrap/             # Application bootstrapping
├── config/                # Configuration files
├── database/
│   ├── migrations/        # Database schema changes
│   ├── seeders/           # Database seeders
│   └── factories/         # Model factories for testing
├── docs/
│   └── adr/              # Architecture Decision Records
├── resources/
│   └── js/
│       ├── components/   # Reusable React components
│       └── pages/        # Inertia page components
├── routes/                # Route definitions
├── tests/                 # Test files (Pest)
└── storage/               # Application storage
```

### Architectural Patterns

**Server-Driven SPA (Inertia)**

- Pages are React components rendered server-side via Inertia
- No separate API for frontend-backend communication
- Uses Ziggy for named route generation in JavaScript

**Domain-Driven Design (Emerging)**

- Models represent domain entities
- Services encapsulate business logic
- Form Requests handle validation and authorization

**Testing Strategy**

- Pest for feature and unit tests
- Feature tests for HTTP endpoints and user flows
- Unit tests for isolated business logic
- Browser tests for critical user journeys

**Code Quality Tools**

- Laravel Pint for code formatting
- PHPStan (via Larastan) for static analysis
- Rector for automated refactoring
- ESLint + Prettier for JavaScript/TypeScript

### Configuration

Key configuration files:

- `bootstrap/app.php` — Application configuration, middleware registration
- `bootstrap/providers.php` — Service provider registration
- `config/*.php` — Package and application configuration
- `.env` — Environment-specific variables (not committed)

### Authentication & Authorization

- **Sanctum** for API token authentication
- **Gates and Policies** for authorization
- **Middleware** for request filtering

### Database Conventions

- Migrations are immutable once deployed
- Foreign key constraints enforced
- Eloquent relationships for associations
- Query builder for complex queries
- Factories and seeders for test data

## Consequences

### Positive

- **Modern stack**: Latest versions of Laravel, React, and Tailwind
- **Developer experience**: Hot reload, type checking, auto-formatting
- **Type safety**: PHP 8.4 features, TypeScript in frontend
- **Testing culture**: Pest provides clean, expressive test syntax
- **Inertia simplicity**: No API layer needed for frontend-backend communication

### Negative

- **Version churn**: Keeping up with latest versions requires maintenance
- **Learning curve**: Team must understand Inertia patterns
- **Tooling complexity**: Multiple linters, formatters, and analyzers to maintain
- **Vendor lock-in**: Heavy reliance on Laravel ecosystem

### Risks

- **Inertia limitations**: Complex real-time features may require API layer
- **Database scaling**: MySQL may need read replicas or sharding at scale
- **Frontend bundle size**: React + Inertia may impact initial load time
- **PHP version upgrades**: Annual PHP upgrades require testing effort

**Mitigation strategies**:

- Monitor bundle size with build analysis
- Plan for horizontal scaling early
- Keep PHP upgrades in CI/CD pipeline
- Consider API resources for future mobile/external integrations

## Compliance

- New architectural decisions should create new ADRs
- Deviations from this architecture require ADR documentation
- Code reviews should verify adherence to established patterns

## Notes

### Alternatives Considered

1. **Separate API + SPA**: Rejected in favor of Inertia for simplicity
2. **Vue.js**: Rejected in favor of React due to team expertise
3. **PostgreSQL**: MySQL chosen for team familiarity and hosting options
4. **Livewire**: Inertia chosen for better frontend flexibility

### Future Considerations

- API versioning strategy if mobile apps are needed
- Event sourcing for complex domain workflows
- Caching strategy (Redis) for performance
- Queue system for background jobs

### Post-ADR-0005 Update (2026-04-01)

The provider abstraction layer shifts from direct cloud API calls (creating VMs, networks, firewalls) to CAPI manifest generation and Kubernetes API interaction. The `CloudProviderFactory` is replaced by a `CloudManager` following Laravel's Manager/Driver pattern (ADR-0009). Provider service contracts are narrowed to read-only inventory queries. See ADR-0005 for details.
