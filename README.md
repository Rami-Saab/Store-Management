<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="350" alt="Laravel Logo" />
  <br/>
  <strong>Multi-Branch Store Management System</strong>
  <br/>
  <sub>Enterprise-grade branch & inventory orchestration platform built with Laravel 10</sub>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/Laravel-10.x-FF2D20?logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Vite-646CFF?logo=vite&logoColor=white" />
  <img src="https://img.shields.io/badge/Pest-Tests-9F4AFB?logo=pest&logoColor=white" />
  <img src="https://img.shields.io/badge/PHPStan-Level%205-7A08FA?logo=phpstan&logoColor=white" />
  <img src="https://img.shields.io/badge/GitHub%20Actions-2088FF?logo=githubactions&logoColor=white" />
</p>

---

## Table of Contents

- [Architectural Excellence](#architectural-excellence)
- [Design Patterns & Principles](#design-patterns--principles)
- [Advanced Features](#advanced-features)
- [Performance Optimizations](#performance-optimizations)
- [Security Architecture](#security-architecture)
- [Type Safety & Code Quality](#type-safety--code-quality)
- [Event-Driven Architecture](#event-driven-architecture)
- [Scalability Considerations](#scalability-considerations)
- [Technology Stack](#technology-stack)
- [License](#license)

---

## Architectural Excellence

### Service-Oriented Architecture (SOA)

The application implements a rigorous **service-layer pattern** that completely decouples business logic from HTTP concerns. Controllers are deliberately kept under 50 lines, serving purely as orchestration layers that delegate to specialized services:

- **`StoreCrudService`** — Handles atomic CRUD operations wrapped in database transactions
- **`StoreSearchService`** — Encapsulates complex search logic with stored procedure integration
- **`StoreBrochureService`** — Manages PDF rendering, caching, and chunked uploads
- **`AssignmentService`** — Coordinates staff-to-branch synchronization with conflict resolution
- **`StoreStatsService`** — Computes dashboard analytics with caching strategies

This architecture enables **horizontal scaling** of business logic, **unit testing** in isolation, and **code reusability** across different interfaces (HTTP, CLI, API).

### Strict Type Safety

Every PHP file declares `strict_types=1`, enforcing compile-time type checking. Models include comprehensive **PHPDoc properties** for IDE autocomplete, and all methods feature complete **type hints**:

```php
declare(strict_types=1);

class Store extends Model
{
    /** @property int $id */
    /** @property string $name */
    /** @property-read Province $province */
    
    public function scopeFilterName(Builder $query, ?string $name): Builder
    {
        // Type-safe query building
    }
}
```

This eliminates entire classes of runtime errors and enables **PHPStan Level 5** static analysis to catch bugs before execution.

---

## Design Patterns & Principles

### SOLID Principles in Practice

**Single Responsibility** — Each service handles one domain concern. `StoreCrudService` only manages persistence, while `StoreBrochureService` only handles file operations.

**Open/Closed** — New features are added through **event listeners** and **middleware** without modifying existing services. The `InvalidateStoreCache` listener automatically handles cache clearing for any store modification.

**Dependency Inversion** — All services depend on abstractions (interfaces) injected via Laravel's container. Controllers receive services through constructor injection, enabling easy mocking and testing.

### Repository Pattern Implementation

The service layer acts as a **repository abstraction**, hiding Eloquent implementation details from controllers. This allows:

- **Swapping data sources** (MySQL → PostgreSQL) without controller changes
- **Caching at the repository level** transparently
- **Complex query composition** in services, keeping controllers thin

### Strategy Pattern for Authorization

The `StoreScopeService` implements a **strategy pattern** for role-based data scoping:

```php
interface StoreScopeStrategy
{
    public function applyScope(Builder $query, User $user): Builder;
}

class AdminScopeStrategy implements StoreScopeStrategy
{
    public function applyScope(Builder $query, User $user): Builder
    {
        return $query; // No restrictions for admins
    }
}

class ManagerScopeStrategy implements StoreScopeStrategy
{
    public function applyScope(Builder $query, User $user): Builder
    {
        return $query->where('manager_id', $user->id);
    }
}
```

This makes adding new roles or modifying access rules **non-breaking** changes.

---

## Advanced Features

### Event-Driven Cache Invalidation

The system implements **automatic cache invalidation** through Laravel's event system:

```php
Event::listen(StoreCreated::class, InvalidateStoreCache::class);
Event::listen(StoreUpdated::class, InvalidateStoreCache::class);
Event::listen(StoreDeleted::class, InvalidateStoreCache::class);
```

When any store modification occurs, the `InvalidateStoreCache` listener automatically clears relevant caches. This **eliminates cache staleness bugs** and removes manual cache clearing from business logic.

### Queue-Based Asynchronous Processing

Heavy operations like brochure uploads are offloaded to **queue jobs**:

```php
class ProcessBrochureUpload implements ShouldQueue
{
    public int $tries = 3;
    public int $timeout = 300;
    
    public function handle(StoreBrochureService $service): void
    {
        // Process PDF asynchronously
    }
}
```

This provides:
- **Non-blocking user experience** — uploads don't tie up HTTP workers
- **Automatic retries** on failure with exponential backoff
- **Horizontal scaling** — multiple queue workers process jobs in parallel
- **Dead letter queues** for failed job analysis

### Chunked File Uploads

Large PDF brochures are uploaded in **chunks** to bypass server size limits:

- **Resume capability** — interrupted uploads can be resumed
- **Memory efficiency** — chunks are processed individually
- **Progress tracking** — real-time upload progress for users
- **Concurrent uploads** — multiple chunks can be uploaded in parallel

### Stored Procedure Integration

Complex store searches are offloaded to **MySQL stored procedures** for performance:

```sql
CREATE PROCEDURE SearchStores(
    IN p_name VARCHAR(255),
    IN p_province_id INT,
    IN p_status VARCHAR(50),
    IN p_phone VARCHAR(20)
)
```

This reduces **N+1 query problems** and leverages database-side optimizations for complex joins and filtering.

---

## Performance Optimizations

### Database-Level Optimizations

- **Eager Loading** — Relations are preloaded using `with()` to prevent N+1 queries
- **Query Scopes** — Reusable query fragments compose complex filters efficiently
- **Indexing Strategy** — Composite indexes on frequently queried columns
- **Stored Procedures** — Complex searches execute on the database server
- **Connection Pooling** — Laravel's connection management reduces overhead

### Caching Strategy

The application implements a **multi-layer caching strategy**:

```php
// Cache store lists for 1 hour
Cache::remember('stores.active', 3600, fn () => 
    Store::active()->with(['province', 'manager'])->get()
);

// Cache individual stores with tags
Cache::tags(['stores'])->remember("store.{$id}", 7200, fn () =>
    Store::with(['employees', 'products'])->findOrFail($id)
);
```

- **Tag-based invalidation** — Clear entire cache groups atomically
- **Automatic expiration** — Time-to-live prevents stale data
- **Cache warming** — Pre-populate caches during deployment

### Frontend Performance

- **Vite** — Fast HMR and optimized production builds
- **Asset Versioning** — Cache-busting through file hashes
- **Lazy Loading** — JavaScript modules loaded on demand
- **Tailwind CSS** — Purge unused styles for minimal CSS size

---

## Security Architecture

### Defense in Depth

The application implements **multiple security layers**:

1. **Rate Limiting** — Configurable limits per endpoint prevent abuse:
   ```php
   RateLimiter::for('login', fn (Request $r) => 
       Limit::perMinute(5)->by($r->ip() . '|' . $r->input('email'))
   );
   ```

2. **Input Validation** — Form Requests sanitize and validate before reaching services
3. **Authorization Gates** — Policies check permissions before data access
4. **SQL Injection Prevention** — Eloquent parameter binding by default
5. **XSS Protection** — Blade auto-escapes output; CSP headers configured
6. **CSRF Protection** — Tokens on all state-changing requests
7. **Audit Trails** — `created_by` and `updated_by` track all modifications

### Role-Based Access Control (RBAC)

Using **Spatie Laravel Permission**, the system implements:

- **Hierarchical roles** — Roles can inherit permissions from parent roles
- **Permission caching** — Permission checks cached for performance
- **Dynamic permissions** — Runtime permission assignment without code changes
- **Middleware integration** — Route-level permission enforcement

### Secure File Handling

- **Storage abstraction** — Files stored outside web root
- **Signed URLs** — Temporary, expiring download links
- **MIME validation** — File type verification on upload
- **Chunked uploads** — Memory-efficient processing

---

## Type Safety & Code Quality

### Static Analysis with PHPStan

PHPStan Level 5 configuration enforces:

- **Strict comparison rules** — Prevents type coercion bugs
- **Deprecation detection** — Identifies outdated code usage
- **Unused code detection** — Removes dead code
- **Generic type checking** — Ensures collection type safety

```bash
composer stan  # Runs PHPStan analysis
```

### Automated Code Formatting

Laravel Pint ensures **consistent code style** across the project:

```bash
composer pint      # Check code style
composer pint:fix  # Auto-fix style issues
```

All code follows **PSR-12** standards automatically.

### Modern Testing with Pest

Pest PHP provides **expressive, readable tests**:

```php
test('admin can create store', function () {
    $admin = User::factory()->admin()->create();
    
    actingAs($admin)
        ->post(route('stores.store'), Store::factory()->raw())
        ->assertRedirect(route('stores.index'));
        
    expect(Store::count())->toBe(1);
});
```

- **Database transactions** — Tests rolled back automatically
- **Factories** — Consistent test data generation
- **Parallel execution** — Tests run concurrently for speed

---

## Event-Driven Architecture

### Domain Events

The system emits **domain events** for significant state changes:

```php
class StoreCreated
{
    public function __construct(
        public readonly Store $store,
        public readonly ?int $actorId = null
    ) {}
}
```

Events are **immutable data carriers** containing all relevant context.

### Event Listeners

Listeners respond to events **asynchronously**:

```php
class InvalidateStoreCache
{
    public function handle(StoreCreated|StoreUpdated|StoreDeleted $event): void
    {
        $this->storeService->flushStoreCaches();
    }
}
```

This enables:
- **Loose coupling** — Event emitters don't know about listeners
- **Extensibility** — Add new listeners without modifying existing code
- **Audit logging** — All state changes can be logged centrally
- **Notifications** — Email/SMS notifications triggered by events

### Event Broadcasting

Events can be **broadcasted to frontend** via Laravel Echo for real-time updates:

```php
class StoreUpdated implements ShouldBroadcast
{
    public function broadcastOn()
    {
        return new Channel('stores');
    }
}
```

---

## Scalability Considerations

### Horizontal Scaling

The architecture supports **horizontal scaling**:

- **Stateless HTTP workers** — No session storage in PHP memory
- **Queue-based processing** — Background jobs distributed across workers
- **Cache layer** — Redis shared across all instances
- **Database connection pooling** — Efficient connection reuse

### Database Scalability

- **Read replicas** — Read queries can be directed to replica servers
- **Sharding readiness** — Store data can be sharded by region
- **Query optimization** — Stored procedures reduce database load
- **Migration strategy** — Zero-downtime migrations supported

### Caching Strategy

- **Multi-tier caching** — Application cache + CDN + browser cache
- **Cache warming** — Pre-populate caches during deployment
- **Cache stampede prevention** — Lock mechanisms prevent thundering herds
- **Cache invalidation** — Tag-based invalidation for granular control

---

## Technology Stack

| Layer | Technology | Rationale |
|-------|------------|-----------|
| Backend | PHP 8.2+ | Latest features, JIT compilation, strict typing |
| Framework | Laravel 10 | Stable LTS release, native support for PHP 8.2 |
| Auth | Laravel Sanctum 3.3 | Lightweight API authentication, SPA support |
| RBAC | Spatie Permission 5.0 | Mature, feature-rich permission system |
| Data Objects | Spatie Data 3.0 | Type-safe DTOs, validation, transformation |
| Frontend | Blade + Alpine.js | Server-side rendering, progressive enhancement |
| Build | Vite 5.0 | Fast HMR, optimized production builds |
| CSS | Tailwind CSS 3.4 | Utility-first, highly customizable |
| PDF | PDF.js 3.11 | Client-side rendering, no server dependencies |
| Headless PDF | Browsershot 3.61 | PDF generation via Chrome headless |
| Testing | Pest PHP 2.0 | Modern syntax, parallel execution |
| Static Analysis | PHPStan 1.10 | Type safety, bug detection |
| Code Formatting | Laravel Pint 1.13 | Automated PSR-12 compliance |
| Database | MySQL 8.0 | JSON support, window functions, CTEs |
| Cache/Queue | Redis | In-memory performance, pub/sub support |

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
