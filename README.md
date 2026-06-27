<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="350" alt="Laravel Logo" />
  <br/>
  <strong>Multi-Branch Store Management System</strong>
  <br/>
  <sub>Enterprise-grade branch & inventory orchestration platform built with Laravel 11</sub>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Vite-646CFF?logo=vite&logoColor=white" />
  <img src="https://img.shields.io/badge/Pest-Tests-9F4AFB?logo=pest&logoColor=white" />
  <img src="https://img.shields.io/badge/PHPStan-Level%208-7A08FA?logo=phpstan&logoColor=white" />
  <img src="https://img.shields.io/badge/Docker-2496ED?logo=docker&logoColor=white" />
  <img src="https://img.shields.io/badge/GitHub%20Actions-2088FF?logo=githubactions&logoColor=white" />
</p>

---

## Table of Contents

- [Overview](#overview)
- [Architecture & Design Philosophy](#architecture--design-philosophy)
- [Core Features](#core-features)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Quick Start](#quick-start)
- [Development with Docker](#development-with-docker)
- [Database Schema](#database-schema)
- [API Documentation](#api-documentation)
- [Security & Authorization](#security--authorization)
- [Testing](#testing)
- [Code Quality](#code-quality)
- [CI/CD Pipeline](#cicd-pipeline)
- [Contributing](#contributing)
- [License](#license)

---

## Overview

The **Store Management System** is a modern, enterprise-grade Laravel 11 application engineered for organizations managing multiple retail branches, warehouses, and product lines. It provides a centralized platform to create, manage, and monitor branches across provinces, assign staff hierarchically, link products with per-branch inventory, and distribute digital brochures — all secured by role-based access control.

Built with **service-layer architecture**, the application isolates business logic from controllers, uses **database transactions** for state-critical mutations, implements **event-driven architecture** for decoupled operations, and leverages **background job processing** for heavy tasks. Every feature is covered by **PHP 8.2 strict typing**, **Form Request validation**, **Eloquent policies**, and **middleware gates** to enforce strict authorization boundaries.

### Key Highlights

- **Laravel 11** with PHP 8.2+ strict typing throughout
- **Service-oriented architecture** with dependency injection
- **Event-driven design** for cache invalidation and notifications
- **Queue-based background processing** for brochure uploads
- **Rate limiting** for API endpoints and sensitive operations
- **Docker Compose** for consistent development environments
- **GitHub Actions CI/CD** with automated testing and quality checks
- **Pest PHP** for modern, expressive testing
- **PHPStan Level 8** for static analysis
- **Laravel Pint** for automated code formatting

---

## Architecture & Design Philosophy

### Layered Responsibility

| Layer | Responsibility |
|-------|----------------|
| **Controllers** | HTTP orchestration only — thin controllers that delegate to services. |
| **Form Requests** | Strict input validation, authorization checks, and data sanitization. |
| **Services** | Encapsulated business logic (CRUD, search, assignments, brochure handling). |
| **Models** | Eloquent relationships, query scopes, accessors/mutators with PHPDoc properties. |
| **Policies** | Authorization gates tied to domain actions (view, create, update, delete). |
| **Middleware** | Role enforcement, rate limiting, and request timing. |
| **Events/Listeners** | Decoupled operations for cache invalidation and notifications. |
| **Jobs** | Background processing for heavy operations (brochure uploads). |

### Key Patterns

- **Repository-like Services** — `StoreCrudService`, `StoreSearchService`, `StoreBrochureService` keep controllers under 50 lines of logic.
- **Database Transactions** — every create/update/delete operation runs inside a transaction to guarantee atomicity.
- **Stored Procedures** — branch search is offloaded to a MySQL stored procedure for complex filtering across relations.
- **Chunked Uploads** — brochure PDFs are uploaded in chunks to bypass server size limits and resume on failure.
- **Scoped Queries** — Eloquent local scopes (`filterName`, `filterProvince`, `filterStatus`, `filterPhone`) compose complex filters declaratively.
- **Event-Driven Cache Invalidation** — `StoreCreated`, `StoreUpdated`, `StoreDeleted` events trigger automatic cache clearing.
- **Queue-Based Processing** — `ProcessBrochureUpload` job handles PDF processing asynchronously.

---

## Core Features

### Branch (Store) Management
- **CRUD Operations** — create, read, update, and delete branches with full audit trails (`created_by`, `updated_by`).
- **Advanced Search** — real-time AJAX grid search by name, province, status, and phone number via stored procedure.
- **Address & Contact Normalization** — automatic phone formatting and English place-name normalization.
- **Status Management** — active, inactive, under_maintenance states with appropriate access controls.

### Staff & Assignment Engine
- **Hierarchical Roles** — `admin`, `store_manager`, `store_employee` with scoped access via `StoreScopeService`.
- **Branch Assignments** — sync managers and employees per branch through a dedicated assignment UI.
- **Department Linking** — branches and users are optionally bound to organizational departments.
- **Permission System** — granular permissions with caching for performance.

### Product-Branch Inventory
- **Product Catalog** — view products and the branches stocking them.
- **Per-Branch Quantity** — `product_store` pivot table tracks stock quantity at each branch.
- **Warehouse Integration** — branches can be linked to multiple warehouses through a `store_warehouse` pivot.

### Digital Brochure Distribution
- **PDF Viewer** — embedded PDF.js viewer for in-app brochure rendering.
- **Chunked Upload** — resilient chunked upload of large brochure PDFs with queue processing.
- **Download & Inline Modes** — serve brochures inline for preview or as attachment for download.
- **Background Processing** — brochure uploads are processed asynchronously via queue jobs.

### Authentication & API
- **Session + Token Auth** — dual authentication with Laravel Sanctum for web sessions and API tokens.
- **Role-Based Middleware** — `EnsureRole` middleware locks store routes to authorized roles only.
- **Tab Session Isolation** — custom `TabSessionCookie` middleware prevents cross-tab session collisions.
- **Rate Limiting** — configurable rate limits for login attempts, API calls, and sensitive operations.

---

## Technology Stack

| Layer | Technology | Version |
|-------|------------|---------|
| Backend | PHP | ^8.2 |
| Framework | Laravel | ^11.0 |
| Auth | Laravel Sanctum | ^4.0 |
| RBAC | Spatie Laravel Permission | ^6.0 |
| Data Objects | Spatie Laravel Data | ^4.0 |
| Frontend | Blade + Alpine.js | -- |
| Build | Vite | ^5.0 |
| CSS Framework | Tailwind CSS | ^3.4 |
| PDF Rendering | PDF.js | ^4.0 |
| Headless PDF | Browsershot | ^4.0 |
| Testing | Pest PHP | ^2.0 |
| Static Analysis | PHPStan | ^1.10 |
| Code Formatting | Laravel Pint | ^1.13 |
| Database | MySQL / MariaDB | ^8.0 |
| Cache/Queue | Redis | -- |
| Containerization | Docker Compose | -- |

---

## Project Structure

```
app/
  Http/
    Controllers/
      Auth/              # StoreAuthController, TokenAuthController
      Products/          # ProductController
      Stores/            # StoreController, StoreDashboardController, StoreProductController
      SearchController.php
    Middleware/
      EnsureRole.php     # Role-based route protection
      RequestTiming.php  # Performance observability
      TabSessionCookie.php
    Requests/
      StoreRequest.php       # Validation rules for store creation
      UpdateStoreRequest.php # Validation rules for store updates
    Resources/
      StoreResource.php      # API response formatting with type hints
  Models/
    Store.php            # Branch model with scopes, relations, PHPDoc
    User.php             # Staff model with role pivot, PHPDoc
    Product.php          # Product catalog
    Province.php         # Geographic hierarchy
    Department.php       # Organizational units
    Warehouse.php        # Inventory locations
    Role.php / Permission.php
  Policies/
    StorePolicy.php      # Authorization rules per action
  Services/
    Store/
      StoreCrudService.php          # Atomic create/update/delete
      StoreSearchService.php        # Advanced search logic
      StoreBrochureService.php      # PDF rendering & download
      StoreService.php              # Business constraints
      StoreStatsService.php         # Dashboard analytics
      AssignmentService.php         # Staff sync logic
  Events/
    StoreCreated.php      # Dispatched on store creation
    StoreUpdated.php      # Dispatched on store update
    StoreDeleted.php      # Dispatched on store deletion
  Listeners/
    InvalidateStoreCache.php  # Auto-clear caches on store changes
  Jobs/
    ProcessBrochureUpload.php    # Async brochure processing
  Providers/
    FortifyServiceProvider.php    # Rate limiting configuration
    EventServiceProvider.php       # Event/listener mappings
database/
  migrations/
    *_create_stores_table.php
    *_create_store_user_table.php
    *_create_product_store_table.php
    *_create_store_warehouse_table.php
    *_create_store_search_procedure.php
  seeders/
    ProvinceSeeder.php
    DepartmentSeeder.php
    StoreRolePermissionSeeder.php
resources/
  views/
    stores/              # index, show, create, edit, assignments, products, brochure
    layouts/app.blade.php
  js/
    app.js               # Vite entry point
  css/
    app.css              # Tailwind CSS entry point
tests/
  Feature/              # Pest feature tests
  Unit/                 # Pest unit tests
.github/
  workflows/
    ci.yml               # GitHub Actions CI/CD pipeline
docker/
  nginx/
    default.conf         # Nginx configuration
Dockerfile             # PHP-FPM container
docker-compose.yml      # Multi-container development environment
vite.config.js         # Vite configuration
phpstan.neon            # PHPStan configuration (Level 8)
pint.json               # Laravel Pint configuration
pest.php                # Pest configuration
```

---

## Quick Start

### Prerequisites

- PHP >= 8.2
- Composer 2.x
- MySQL >= 8.0 or MariaDB >= 10.5
- Node.js >= 18.x & NPM >= 9.x
- Redis (optional, for cache/queue)

### Installation

```bash
# Clone the repository
git clone https://github.com/Rami-Saab/Store-Management.git
cd Store-Management

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=store_management
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Run migrations and seeders
php artisan migrate --seed

# Create storage link
php artisan storage:link

# Build frontend assets
npm run build

# Start development server
php artisan serve
```

Visit `http://localhost:8000` in your browser.

---

## Development with Docker

### Using Docker Compose

```bash
# Start all services
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate --seed

# Run tests
docker-compose exec app composer test

# View logs
docker-compose logs -f app
```

Services included:
- **app** — PHP 8.2 FPM with Laravel
- **nginx** — Web server
- **db** — MySQL 8.0
- **redis** — Cache and queue
- **queue** — Laravel queue worker

---

## Database Schema

### Core Tables

| Table | Purpose |
|-------|---------|
| `users` | Staff accounts with role pivots |
| `roles` / `permissions` | RBAC definitions (Spatie Permission) |
| `role_user` | User-to-role assignments |
| `permission_role` | Role-to-permission mappings |
| `stores` | Branch records with audit fields |
| `provinces` | Geographic hierarchy |
| `departments` | Organizational units |
| `products` | Product catalog |
| `product_store` | Many-to-many with stock `quantity` |
| `warehouses` | Inventory locations |
| `store_warehouse` | Branch-to-warehouse links |
| `store_user` | Branch staff assignments |
| `assignment_requests` | Assignment change workflow |

### Stored Procedure

`SearchStores` — parameterized search across stores by name, province, status, and phone digits, returning paginated results with manager and province preloaded for optimal performance.

---

## API Documentation

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/login` | Session login |
| POST | `/logout` | Session logout |
| POST | `/auth/token/login` | Sanctum token login |
| GET  | `/auth/token` | Issue token from session |
| POST | `/auth/token/logout` | Revoke token |

### Store Management (requires `admin`, `store_manager`, or `store_employee`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET    | `/stores` | Grid listing with AJAX search |
| GET    | `/stores/create` | Create form |
| POST   | `/stores` | Persist new branch |
| GET    | `/stores/{store}` | Branch detail |
| GET    | `/stores/{store}/edit` | Edit form |
| PUT    | `/stores/{store}` | Update branch |
| DELETE | `/stores/{store}` | Delete (guarded by constraints) |
| GET    | `/stores/{store}/assignments` | Staff assignment page |
| PUT    | `/stores/{store}/assignments` | Sync manager + employees |
| GET    | `/stores/{store}/products` | Linked products page |
| PUT    | `/stores/{store}/products` | Sync products + quantities |
| GET    | `/stores/{store}/brochure` | Inline PDF viewer |
| GET    | `/stores/{store}/brochure/download` | Download PDF |
| POST   | `/stores/brochure/upload-chunk` | Chunked upload |

### General

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET    | `/dashboard` | Analytics dashboard |
| GET    | `/search` | Global search |
| GET    | `/products/{product}` | Product detail with branch links |

### Rate Limits

| Limiter | Limit | Scope |
|---------|-------|-------|
| `login` | 5/min | IP + email |
| `api` | 60/min | User ID or IP |
| `store-search` | 30/min | User ID or IP |
| `brochure-upload` | 10/hour | User ID or IP |

---

## Security & Authorization

### Role Hierarchy

| Role | Capabilities |
|------|--------------|
| `admin` | Full CRUD, assignments, product linking, brochure upload |
| `store_manager` | View assigned branches, limited edit, manage own staff |
| `store_employee` | View-only access to assigned branches |

### Defense Layers

1. **Middleware** — `EnsureRole` validates role before route entry.
2. **Form Requests** — `StoreRequest` / `UpdateStoreRequest` sanitize and validate all input.
3. **Policies** — `StorePolicy` checks ownership, role scope, and state constraints before action.
4. **Transactions** — `StoreCrudService` wraps mutations in DB transactions to prevent partial writes.
5. **Input Normalization** — `EnglishInputNormalizer` and `UserContact::phone()` standardize text and phone data.
6. **Rate Limiting** — Configurable limits prevent brute-force attacks and API abuse.
7. **Event-Driven Security** — Automatic cache invalidation on data changes prevents stale data exposure.

---

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific test file
./vendor/bin/pest tests/Feature/StorePolicyTest.php
```

### Test Structure

```
tests/
  Feature/
    StorePolicyTest.php      # Authorization tests
    StoreCrudTest.php       # CRUD operation tests
    AssignmentTest.php      # Staff assignment tests
  Unit/
    StoreServiceTest.php    # Service layer tests
    UserTest.php           # User model tests
```

### Testing Best Practices

- **Pest PHP** for expressive, readable test syntax
- **Database transactions** rolled back after each test
- **Factories** for consistent test data
- **RefreshDatabase** trait for clean test state

---

## Code Quality

### Static Analysis

```bash
# Run PHPStan (Level 8)
composer stan
```

PHPStan is configured at **Level 8** with strict rules:
- Deprecation rules
- Strict comparison rules
- No unsafe usage
- Full type coverage

### Code Formatting

```bash
# Check code style
composer pint

# Fix code style
composer pint:fix
```

Laravel Pint ensures consistent code style across the project following PSR-12.

### Type Safety

- **Strict types** (`declare(strict_types=1)`) in all PHP files
- **Full type hints** on all methods and properties
- **PHPDoc properties** on models for IDE autocomplete
- **Return types** on all functions and methods

---

## CI/CD Pipeline

### GitHub Actions

The project includes a comprehensive CI/CD pipeline:

```yaml
- Matrix testing across PHP 8.2 and 8.3
- Composer dependency installation
- Laravel Pint code style check
- PHPStan static analysis
- Pest test execution with coverage
- Codecov coverage reporting
```

### Pipeline Stages

1. **Setup** — Install PHP and dependencies
2. **Lint** — Run Laravel Pint
3. **Analyze** — Run PHPStan
4. **Test** — Run Pest with coverage
5. **Report** — Upload coverage to Codecov

---

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Write tests for your changes
4. Ensure code passes PHPStan and Pint
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

### Development Workflow

```bash
# Create feature branch
git checkout -b feature/my-feature

# Make changes
# ...

# Run quality checks
composer pint
composer stan
composer test

# Commit and push
git add .
git commit -m "feat: add my feature"
git push origin feature/my-feature
```

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
