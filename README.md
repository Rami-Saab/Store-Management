# Store Management System

Enterprise-grade multi-branch retail management platform built on Laravel 10.

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/Laravel-10.48-FF2D20?logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/PHPUnit-Tests-9F4AFB?logo=phpunit&logoColor=white" />
  <img src="https://img.shields.io/badge/PHPStan-Level%205-7A08FA?logo=phpstan&logoColor=white" />
</p>

## System Architecture

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

## Table of Contents

- [Architecture Overview](#architecture-overview)
- [Getting Started](#getting-started)
- [Environment Configuration](#environment-configuration)
- [Core Architecture](#core-architecture)
- [Performance Strategy](#performance-strategy)
- [Security Model](#security-model)
- [Deployment & Scaling](#deployment--scaling)
- [Technology Stack](#technology-stack)

## Architecture Overview

### Service-Oriented Architecture

The application implements a strict service-layer pattern that decouples business logic from HTTP concerns:

- **Controllers** (<50 lines): Pure orchestration layers delegating to services
- **Services**: Domain logic encapsulation with transaction management
- **Repositories**: Data access abstraction (Eloquent wrapper)
- **Events**: Domain state changes for loose coupling
- **Listeners**: Asynchronous side effects (cache invalidation, notifications)

### Design Patterns

| Pattern | Implementation | Benefit |
|---------|---------------|---------|
| Repository | Service layer as data abstraction | Swappable data sources, testability |
| Strategy | Role-based scoping via `StoreScopeStrategy` | Non-breaking role additions |
| Observer | Event-driven cache invalidation | Automatic cache consistency |
| Factory | Domain object creation | Consistent object instantiation |
| Decorator | Middleware pipeline | Cross-cutting concerns |

## Getting Started

### Prerequisites

- PHP >= 8.2
- Composer >= 2.0
- MySQL >= 8.0
- Node.js >= 18
- Redis (optional, for cache/queue)

### Installation

```bash
# Clone repository
git clone https://github.com/Rami-Saab/Store-Management.git
cd Store-Management

# Install dependencies
composer install --no-interaction
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database configuration
# Edit .env with your database credentials
php artisan migrate --force
php artisan db:seed --force

# Build assets
npm run build

# Clear caches
php artisan optimize:clear
```

### Development Server

```bash
php artisan serve
npm run dev
```

## Environment Configuration

### Required Environment Variables

```env
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

# Cache
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Production Configuration

```env
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=stack

# Performance
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Security
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
```

## Core Architecture

### Service Layer Pattern

Controllers delegate to specialized services:

```php
// Controller: Pure orchestration
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
}

// Service: Business logic with transactions
class StoreCrudService
{
    public function create(array $data): Store
    {
        return DB::transaction(fn () => 
            Store::create($data)
        );
    }
}
```

### Event-Driven Architecture

Domain events trigger side effects:

```php
// Event emission
Event::dispatch(new StoreCreated($store, auth()->id()));

// Listener: Cache invalidation
class InvalidateStoreCache
{
    public function handle(StoreCreated|StoreUpdated|StoreDeleted $event): void
    {
        Cache::tags(['stores'])->flush();
    }
}
```

### Type Safety

All files declare `strict_types=1` with comprehensive type hints:

```php
declare(strict_types=1);

class StoreService
{
    public function update(int $id, array $data): Store
    {
        $store = Store::findOrFail($id);
        $store->update($data);
        return $store;
    }
}
```

## Performance Strategy

### Database Optimization

- **Eager Loading**: Relations preloaded via `with()` to prevent N+1
- **Query Scopes**: Reusable query fragments for complex filters
- **Composite Indexes**: Strategic indexing on frequently queried columns
- **Stored Procedures**: Complex searches executed database-side
- **Connection Pooling**: Laravel's connection management reduces overhead

### Caching Strategy

Multi-layer caching with tag-based invalidation:

```php
// Application cache with tags
Cache::tags(['stores'])->remember("store.{$id}", 7200, fn () =>
    Store::with(['employees', 'products'])->findOrFail($id)
);

// Cache invalidation
Cache::tags(['stores'])->flush();
```

### Queue-Based Processing

Heavy operations offloaded to queue workers:

```php
class ProcessBrochureUpload implements ShouldQueue
{
    public int $tries = 3;
    public int $timeout = 300;
    public int $backoff = [10, 30, 60];
}
```

## Security Model

### Defense in Depth

1. **Rate Limiting**: Per-endpoint limits prevent abuse
2. **Input Validation**: Form Requests sanitize before services
3. **Authorization Gates**: Policies check permissions pre-access
4. **SQL Injection**: Eloquent parameter binding by default
5. **XSS Protection**: Blade auto-escapes; CSP headers configured
6. **CSRF Protection**: Tokens on all state-changing requests
7. **Audit Trails**: `created_by`/`updated_by` track modifications

### RBAC Implementation

Using Spatie Laravel Permission:

- **Hierarchical roles**: Roles inherit from parent roles
- **Permission caching**: Checks cached for performance
- **Dynamic permissions**: Runtime assignment without code changes
- **Middleware integration**: Route-level enforcement

### Secure File Handling

- **Storage abstraction**: Files outside web root
- **Signed URLs**: Temporary, expiring download links
- **MIME validation**: File type verification on upload
- **Chunked uploads**: Memory-efficient processing

## Deployment & Scaling

### Horizontal Scaling

The architecture supports horizontal scaling through:

- **Stateless workers**: No session storage in PHP memory
- **Shared cache**: Redis across all instances
- **Queue distribution**: Background jobs across workers
- **Connection pooling**: Efficient database connection reuse

### Database Scaling

- **Read replicas**: Read queries directed to replicas
- **Sharding readiness**: Data sharded by region
- **Query optimization**: Stored procedures reduce load
- **Zero-downtime migrations**: Supported via Laravel

### Production Deployment

```bash
# Optimization
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue workers
php artisan queue:work --daemon --tries=3 --timeout=300

# Supervisor configuration (recommended)
[program:store-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/app/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=5
redirect_stderr=true
stdout_logfile=/var/www/app/storage/logs/worker.log
```

### CI/CD Pipeline

GitHub Actions workflow includes:

- **Linting**: PHPStan Level 5 analysis
- **Testing**: PHPUnit with coverage
- **Security**: Dependency vulnerability scanning
- **Build**: Asset compilation
- **Deployment**: Automated staging/production

## Technology Stack

| Layer | Technology | Rationale |
|-------|------------|-----------|
| Backend | PHP 8.2+ | JIT compilation, strict typing, modern features |
| Framework | Laravel 10.48 | Stable LTS, PHP 8.2 native support |
| Auth | Laravel Sanctum 3.3 | Lightweight API auth, SPA support |
| RBAC | Spatie Permission 5.0 | Mature permission system |
| Data Objects | Spatie Data 3.0 | Type-safe DTOs, validation |
| Frontend | Blade + Alpine.js | SSR, progressive enhancement |
| Build | Vite 5.0 | Fast HMR, optimized builds |
| CSS | Tailwind CSS 3.4 | Utility-first, customizable |
| PDF | PDF.js 3.11 | Client-side rendering |
| Testing | PHPUnit 10.0 | Mature testing framework |
| Static Analysis | PHPStan 1.10 | Type safety, bug detection |
| Code Formatting | Laravel Pint 1.13 | PSR-12 compliance |
| Database | MySQL 8.0 | JSON, window functions, CTEs |
| Cache/Queue | Redis | In-memory performance, pub/sub |

## Code Quality Standards

### Static Analysis

```bash
# PHPStan Level 5 analysis
composer stan

# Laravel Pint formatting
composer pint:fix
```

### Testing

```bash
# Run all tests
composer test

# With coverage
composer test:coverage
```

### Quality Gates

- PHPStan Level 5: No errors allowed
- Test coverage: >80% for critical paths
- PSR-12 compliance: Enforced via Pint
- Security advisories: Blocked on critical CVEs

## License

MIT License - see [LICENSE](LICENSE) for details.
