# LaraKube

A Developer Internal Portal that bridges DevOps and Developers. Organizations provision Kubernetes infrastructure across cloud providers, while developers deploy applications Vercel and Laravel Cloud style — one PR/MR is all it takes.

LaraKube removes the complexity of managing infrastructure, application deployments, build processes, security checks, and governance — so your team ships faster without compromising on reliability.

## Features

- **Multi-Cloud Providers** — Connect Hetzner, DigitalOcean, and more from a single platform
- **Organization Management** — Teams and roles with multi-tenant isolation
- **Server Provisioning** — Create, list, sync, and manage servers across providers
- **Web Dashboard** — Built with React, Inertia.js, and Tailwind CSS
- **CLI Interface** — Full-featured Artisan commands for infrastructure management
- **API Token Validation** — Tokens are verified against provider APIs on creation and encrypted at rest

## Requirements

- PHP 8.4+
- Node.js 22+
- Composer
- SQLite (default) or MySQL/PostgreSQL

## Installation

```bash
git clone https://github.com/your-org/larakube.git
cd larakube

composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate

npm run build
```

## Development

Start all services (web server, queue, logs, and Vite) with a single command:

```bash
composer run dev
```

Or run them individually:

```bash
php artisan serve     # Web server
npm run dev           # Vite dev server
```

## CLI Usage

### Authentication

```bash
php artisan user:register          # Create an account
php artisan user:login             # Log in
php artisan user:logout            # Log out
```

### Organizations

```bash
php artisan organization:create    # Create a new organization
php artisan organization:select    # Switch active organization
```

### Cloud Providers

```bash
php artisan cloud-provider:add     # Connect a cloud provider
php artisan cloud-provider:list    # List connected providers
php artisan cloud-provider:remove  # Disconnect a provider
```

### Servers

```bash
php artisan server:create          # Create a new server
php artisan server:list            # List and sync servers
php artisan server:show            # Show server details
php artisan server:delete          # Delete a server
```

## Testing

Run the full test suite:

```bash
composer test
```

This runs type coverage, unit/feature tests with 100% code coverage, Pint, Rector, ESLint, PHPStan, and TypeScript checks.

Individual checks:

```bash
composer test:type-coverage        # Pest type coverage (100%)
composer test:unit                 # Pest tests with code coverage (100%)
composer test:lint                 # Pint + Rector + ESLint
composer test:types                # PHPStan + TypeScript
```

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 |
| Frontend | React 19, Inertia.js v2, Tailwind CSS v4 |
| Testing | Pest 4, PHPStan (Larastan), Rector |
| Auth | Laravel Sanctum |
| Code Style | Laravel Pint, ESLint, Prettier |

## License

LaraKube is open-sourced software licensed under the [MIT license](LICENSE).
