# Project Overview: LaraKube

LaraKube is a Developer Internal Portal designed to streamline DevOps and development workflows for Kubernetes infrastructure and application deployment. It enables organizations to provision Kubernetes clusters across various cloud providers and allows developers to deploy applications in a "Vercel and Laravel Cloud style" through a simplified PR/MR process. The project aims to reduce the complexity of infrastructure management, application deployments, build processes, security, and governance, allowing teams to ship faster with enhanced reliability.

**Main Technologies:**
*   **Backend:** Laravel 12, PHP 8.2+
*   **Frontend:** React 19, Inertia.js v2, Tailwind CSS v4
*   **Testing:** Pest 4 (with type coverage, unit/feature testing), PHPUnit
*   **Code Quality:** PHPStan (Larastan), Rector, Laravel Pint, ESLint, Prettier, TypeScript
*   **Build Tool:** Vite

## Building and Running

### Requirements
*   PHP 8.4+
*   Node.js 22+
*   Composer
*   SQLite (default) or MySQL/PostgreSQL

### Installation

To set up the project locally, follow these steps:

```bash
git clone https://github.com/runtimephp/larakube.git
cd larakube

composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate

npm run build
```

### Development

To start all services (web server, queue, logs, and Vite) for development:

```bash
composer run dev
```

This command concurrently runs:
*   `php artisan serve` (Web server)
*   `php artisan queue:listen --tries=1` (Queue listener)
*   `php artisan pail --timeout=0` (Log tailing)
*   `npm run dev` (Vite dev server)

## Development Conventions

### PHP
*   **Code Style:** Enforced using [Laravel Pint](https://laravel.com/docs/pint) with rules defined in `pint.json`.
*   **Static Analysis:** Performed with [PHPStan](https://phpstan.org/) (via Larastan) using configuration in `phpstan.neon`.
*   **Refactoring:** Utilizes [Rector](https://getrector.org/) for automated code refactoring.
*   **Strict Types:** `declare(strict_types=1);` is used in PHP files.
*   **Dependency Injection:** Extensive use of Laravel's Service Container for managing dependencies, as seen in `AppServiceProvider.php`.

### JavaScript/TypeScript
*   **Linting:** Enforced using [ESLint](https://eslint.org/) with configuration in `eslint.config.js`.
*   **Formatting:** Handled by [Prettier](https://prettier.io/) with configuration in `.prettierrc`.
*   **Type Checking:** Uses [TypeScript](https://www.typescriptlang.org/) for static type checking.
*   **Frontend Frameworks:** React and Inertia.js are used for building the user interface.
*   **Styling:** [Tailwind CSS](https://tailwindcss.com/) is used for utility-first styling.
*   **Asset Bundling:** [Vite](https://vitejs.dev/) is used for fast development and optimized builds.

### Testing
*   **Framework:** [Pest PHP](https://pestphp.com/) is the primary testing framework for PHP.
*   **Test Suite:** The full test suite can be run with `composer test`, which includes:
    *   Type coverage (`pest --type-coverage --min=100`)
    *   Unit and Feature tests with code coverage (`pest --parallel --coverage --exactly=100.0`)
    *   Linting checks (Pint, Rector, ESLint)
    *   PHPStan and TypeScript checks
*   **Test Configuration:** `tests/Pest.php` defines global test setup, including refreshing the database, preventing stray HTTP requests and processes, and configuring Inertia SSR.

### Project Structure Highlights
*   `app/`: Contains core application logic, including Actions, Clients, Contracts, Data Transfer Objects, HTTP controllers, Models, and Services.
*   `config/`: Laravel configuration files.
*   `database/`: Migrations, factories, and seeders.
*   `resources/js/`: Frontend React/Inertia.js application source.
*   `routes/`: Web, API, console, and authentication routes.
*   `tests/`: Unit and Feature tests.
*   `public/`: Web server root.
*   `infrastructure/`: Contains cloud-init scripts and Ansible playbooks.
