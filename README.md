<div align="center">

# 🏪 Store Management System

**Enterprise-grade multi-branch retail management platform**

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10.48-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Redis](https://img.shields.io/badge/Redis-Cache%2FQueue-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%205-7A08FA?style=for-the-badge)](https://phpstan.org)
[![PHPUnit](https://img.shields.io/badge/Coverage-80%25+-9F4AFB?style=for-the-badge&logo=php)](https://phpunit.de)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

*A production-ready platform for managing multi-branch retail operations — built with clean architecture, RBAC, event-driven design, and full observability.*

[Features](#-features) · [Architecture](#-architecture) · [Getting Started](#-getting-started) · [API Reference](#-api-reference) · [Deployment](#-deployment) · [Contributing](#-contributing)

</div>

---

## 📌 Overview

Store Management System is a **production-grade** Laravel application designed for enterprises operating multiple retail branches. It handles everything from inventory and staff management to audit trails and real-time cache invalidation — all behind a strict service-layer architecture, role-based access control, and a robust CI/CD pipeline.

**What makes this different from a typical CRUD app:**

- Domain logic lives exclusively in services — controllers are pure orchestrators (<50 lines each)
- All state changes emit typed domain events; side effects are handled by async listeners
- Zero N+1 queries by design — enforced through eager loading and query scope patterns
- Role and permission checks cached at the middleware layer — zero runtime permission queries on hot paths
- Horizontal scaling out of the box — stateless workers, shared Redis, distributed queues

---

## ✨ Features

| Domain | Capabilities |
|---|---|
| **Branch Management** | Multi-branch CRUD, scoped data access per role |
| **Staff & RBAC** | Hierarchical roles, granular permissions via Spatie Permission |
| **Inventory** | Product tracking, brochure uploads with async processing |
| **Search** | Full-text and filtered search via stored procedures |
| **Audit Trail** | `created_by` / `updated_by` on every mutation |
| **File Handling** | Chunked uploads, signed URLs, MIME validation |
| **Notifications** | Event-driven, queue-backed notification system |
| **Observability** | Structured logging, queue monitoring, cache telemetry |

---

## 🏗 Architecture

### System Topology

```
┌─────────────────────────────────────────────────────────────┐
│                     Load Balancer / CDN                     │
└──────────────────────┬──────────────────────────────────────┘
                       │
        ┌──────────────┼──────────────┐
        │              │              │
┌───────▼──────┐ ┌────▼─────┐ ┌────▼─────┐
│  Web Server  │ │  Queue   │ │  Redis   │
│   (PHP-FPM)  │ │  Worker  │ │  Cache   │
└───────┬──────┘ └────┬─────┘ └────┬─────┘
        │              │              │
        └──────────────┼──────────────┘
                       │
              ┌────────▼────────┐
              │   MySQL 8.0     │
              │   (Primary)     │
              └─────────────────┘
```

### Layer Responsibilities

```
HTTP Request
    │
    ▼
[Middleware]          ← Rate limiting, auth, RBAC enforcement
    │
    ▼
[Controller]          ← Request validation, delegation only (<50 lines)
    │
    ▼
[Service Layer]       ← All business logic, DB transactions
    │
    ▼
[Repository/Model]    ← Eloquent, query scopes, eager loading
    │
    ▼
[Event Dispatcher]    ← Domain events on every state change
    │
    ▼
[Listeners/Jobs]      ← Async: cache invalidation, notifications
```

### Design Patterns Applied

| Pattern | Where | Why |
|---|---|---|
| **Service Layer** | `app/Services/` | Decouples HTTP from domain logic |
| **Repository** | Service as data abstraction | Swappable data sources, testability |
| **Strategy** | `StoreScopeStrategy` | Non-breaking role-based data scoping |
| **Observer** | Domain events → listeners | Automatic cache consistency |
| **Factory** | Domain object creation | Consistent, validated instantiation |
| **Decorator** | Middleware pipeline | Cross-cutting concerns without inheritance |

---

## 🚀 Getting Started

### Prerequisites

| Requirement | Version |
|---|---|
| PHP | >= 8.2 |
| Composer | >= 2.0 |
| MySQL | >= 8.0 |
| Redis | Any stable |
| Node.js | >= 18 (for Vite) |

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/Rami-Saab/Store-Management.git
cd Store-Management

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Configure environment
cp .env.example .env
php artisan key:generate

# 5. Set up the database
php artisan migrate --seed

# 6. Build frontend assets
npm run build

# 7. Start the development server
php artisan serve
```

### Running Queue Workers

```bash
php artisan queue:work --tries=3 --timeout=300
```

---

## ⚙️ Configuration

### Environment Variables

```dotenv
# Application
APP_NAME="Store Management"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=store_management
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Cache & Queue (Redis recommended)
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Production Hardening

```dotenv
APP_ENV=production
APP_DEBUG=false

SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
LOG_CHANNEL=stack
```

---

## 🔐 Security Model

Security is implemented as **defense in depth** — every layer adds an independent control:

```
1. Rate Limiting       → Per-endpoint throttling via middleware
2. Input Validation    → Form Requests sanitize before reaching services  
3. Authorization       → Policies enforce permissions pre-access
4. SQL Injection       → Eloquent parameter binding throughout
5. XSS Protection      → Blade auto-escape + CSP headers
6. CSRF Protection     → Tokens on all state-mutating requests
7. File Security       → Files outside web root, signed URLs, MIME validation
8. Audit Trail         → created_by / updated_by on every write
```

### RBAC

Powered by [Spatie Laravel Permission](https://github.com/spatie/laravel-permission):

- Hierarchical roles with inheritance
- Permission checks cached — no runtime DB queries on hot paths
- Dynamic permission assignment without code changes
- Route-level enforcement via middleware

---

## ⚡ Performance

### Database

- **Eager loading** — all relations preloaded via `with()`, zero N+1 in production
- **Query scopes** — reusable, composable query fragments
- **Composite indexes** — strategic indexing on all frequently filtered columns
- **Stored procedures** — complex search logic pushed to the DB layer
- **Read replicas** — read queries separated from write path in production

### Caching

```php
// Tag-based application cache — 2 hour TTL
Cache::tags(['stores'])->remember("store.{$id}", 7200, fn () =>
    Store::with(['employees', 'products'])->findOrFail($id)
);

// Automatic invalidation on any mutation
Cache::tags(['stores'])->flush();
```

### Async Processing

Heavy operations are offloaded to queue workers:

```php
class ProcessBrochureUpload implements ShouldQueue
{
    public int $tries   = 3;
    public int $timeout = 300;
    public array $backoff = [10, 30, 60];
}
```

---

## 🛠 Code Architecture Examples

### Controller — Pure Orchestration

```php
class StoreController extends Controller
{
    public function __construct(
        private readonly StoreCrudService $crudService,
        private readonly StoreSearchService $searchService
    ) {}

    public function index(Request $request): Response
    {
        return $this->searchService->search($request->validated());
    }

    public function store(StoreRequest $request): Response
    {
        return $this->crudService->create($request->validated());
    }
}
```

### Service — Business Logic with Transactions

```php
declare(strict_types=1);

class StoreCrudService
{
    public function create(array $data): Store
    {
        return DB::transaction(function () use ($data) {
            $store = Store::create($data);
            Event::dispatch(new StoreCreated($store, auth()->id()));
            return $store;
        });
    }
}
```

### Event-Driven Cache Invalidation

```php
// Listener handles multiple related events
class InvalidateStoreCache
{
    public function handle(StoreCreated|StoreUpdated|StoreDeleted $event): void
    {
        Cache::tags(['stores'])->flush();
    }
}
```

---

## 🧪 Testing & Quality

### Run Tests

```bash
# Full test suite
composer test

# With HTML coverage report
composer test:coverage
```

### Static Analysis

```bash
# PHPStan Level 5 — zero errors allowed
composer stan

# PSR-12 formatting via Laravel Pint
composer pint:fix
```

### Quality Gates

| Gate | Standard |
|---|---|
| PHPStan | Level 5, zero errors |
| Test Coverage | > 80% on critical paths |
| Code Style | PSR-12 via Laravel Pint |
| Dependencies | No critical CVEs (blocked in CI) |

---

## 🚢 Deployment

### Production Optimization

```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Supervisor (Queue Workers)

```ini
[program:store-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/app/artisan queue:work --sleep=3 --tries=3 --timeout=300
autostart=true
autorestart=true
user=www-data
numprocs=5
redirect_stderr=true
stdout_logfile=/var/www/app/storage/logs/worker.log
```

### CI/CD Pipeline (GitHub Actions)

```
Push → Lint (PHPStan) → Test (PHPUnit) → Security Scan → Build Assets → Deploy
```

Every PR is blocked if any gate fails.

### Horizontal Scaling

The architecture is stateless by design:

- **No in-process session state** — all sessions in Redis
- **Shared cache** — Redis accessible from all instances
- **Distributed queues** — workers scale independently
- **Read replicas** — read traffic distributed across replicas
- **Zero-downtime migrations** — supported natively via Laravel

---

## 🧰 Tech Stack

| Layer | Technology | Version |
|---|---|---|
| Language | PHP | 8.2+ |
| Framework | Laravel | 10.48 |
| Authentication | Laravel Sanctum | 3.3 |
| Authorization | Spatie Permission | 5.0 |
| Data Objects | Spatie Data | 3.0 |
| Frontend | Blade + Alpine.js | — |
| Build Tool | Vite | 5.0 |
| CSS | Tailwind CSS | 3.4 |
| PDF Rendering | PDF.js | 3.11 |
| Testing | PHPUnit | 10.0 |
| Static Analysis | PHPStan | 1.10 |
| Code Style | Laravel Pint | 1.13 |
| Database | MySQL | 8.0 |
| Cache / Queue | Redis | Stable |

---

## 📁 Project Structure

```
app/
├── Console/          # Artisan commands
├── Events/           # Domain events (StoreCreated, StoreUpdated, ...)
├── Http/
│   ├── Controllers/  # Thin orchestrators only
│   ├── Middleware/   # Auth, rate limiting, RBAC
│   └── Requests/     # Form validation
├── Jobs/             # Queue-backed heavy operations
├── Listeners/        # Event handlers (cache, notifications)
├── Models/           # Eloquent models with scopes
├── Policies/         # Authorization policies
└── Services/         # All business logic lives here
    ├── StoreCrudService.php
    └── StoreSearchService.php
```

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feat/your-feature`
3. Write tests for new behavior
4. Ensure all quality gates pass: `composer stan && composer test`
5. Submit a pull request with a clear description

Please follow PSR-12 and the existing service-layer conventions.

---

## 📄 License

Distributed under the MIT License. See [LICENSE](LICENSE) for full terms.

---

<div align="center">

Built with precision by [Rami Saab](https://github.com/Rami-Saab)

</div>
