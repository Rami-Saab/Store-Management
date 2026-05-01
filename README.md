<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="350" alt="Laravel Logo" />
  <br/>
  <strong>Multi-Branch Store Management System</strong>
  <br/>
  <sub>Enterprise-grade branch & inventory orchestration platform</sub>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/Laravel-9.x-FF2D20?logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Vite-Frontend-646CFF?logo=vite&logoColor=white" />
  <img src="https://img.shields.io/badge/PHPUnit-Tests-6C4AB6?logo=php&logoColor=white" />
</p>

---

## Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Database](#database)
- [API](#api)
- [Security](#security)
- [Testing](#testing)
- [Screenshots](#screenshots)
- [License](#license)

---

## Overview

The **Store Management System** is a full-stack Laravel application engineered for enterprises operating multiple retail branches, warehouses, and product lines. It provides a centralized dashboard to create, manage, and monitor branches across provinces, assign staff hierarchically, link products with per-branch inventory, and distribute digital brochures -- all secured by role-based access control.

Built with **service-layer architecture**, the application isolates business logic from controllers, uses **database transactions** for state-critical mutations, and implements **stored procedures** for high-performance search. Every feature is covered by **Form Request validation**, **Eloquent policies**, and **middleware gates** to enforce strict authorization boundaries.

---

## Architecture

### Layered Responsibility

| Layer | Responsibility |
|-------|----------------|
| **Controllers** | HTTP orchestration only -- thin controllers that delegate to services. |
| **Form Requests** | Strict input validation, authorization checks, and data sanitization. |
| **Services** | Encapsulated business logic (CRUD, search, assignments, brochure handling). |
| **Models** | Eloquent relationships, query scopes, accessors/mutators. |
| **Policies** | Authorization gates tied to domain actions (view, create, update, delete). |
| **Middleware** | Role enforcement (`admin`, `store_manager`, `store_employee`) and request timing. |

### Key Patterns

- **Repository-like Services** -- `StoreCrudService`, `StoreSearchService`, `StoreBrochureService`, etc., keep controllers under 50 lines of logic.
- **Database Transactions** -- every create/update/delete operation runs inside a transaction to guarantee atomicity.
- **Stored Procedures** -- branch search is offloaded to a MySQL stored procedure for complex filtering across relations.
- **Chunked Uploads** -- brochure PDFs are uploaded in chunks to bypass server size limits and resume on failure.
- **Scoped Queries** -- Eloquent local scopes (`filterName`, `filterProvince`, `filterStatus`, `filterPhone`) compose complex filters declaratively.

---

## Features

### Branch (Store) Management
- **CRUD Operations** -- create, read, update, and delete branches with full audit trails (`created_by`, `updated_by`).
- **Advanced Search** -- real-time AJAX grid search by name, province, status, and phone number via stored procedure.
- **Address & Contact Normalization** -- automatic phone formatting and English place-name normalization.

### Staff & Assignment Engine
- **Hierarchical Roles** -- `admin`, `store_manager`, `store_employee` with scoped access via `StoreScopeService`.
- **Branch Assignments** -- sync managers and employees per branch through a dedicated assignment UI.
- **Department Linking** -- branches and users are optionally bound to organizational departments.

### Product-Branch Inventory
- **Product Catalog** -- view products and the branches stocking them.
- **Per-Branch Quantity** -- `product_store` pivot table tracks stock quantity at each branch.
- **Warehouse Integration** -- branches can be linked to multiple warehouses through a `store_warehouse` pivot.

### Digital Brochure Distribution
- **PDF Viewer** -- embedded PDF.js viewer for in-app brochure rendering.
- **Chunked Upload** -- resilient chunked upload of large brochure PDFs.
- **Download & Inline Modes** -- serve brochures inline for preview or as attachment for download.

### Authentication & API
- **Session + Token Auth** -- dual authentication with Laravel Sanctum for web sessions and API tokens.
- **Role-Based Middleware** -- `EnsureRole` middleware locks store routes to authorized roles only.
- **Tab Session Isolation** -- custom `TabSessionCookie` middleware prevents cross-tab session collisions.

---

## Tech Stack

| Layer | Technology | Version |
|-------|------------|---------|
| Backend | PHP | ^8.0 |
| Framework | Laravel | ^9.0 |
| Auth | Laravel Sanctum | ^2.14 |
| Frontend | Blade + Vanilla JS | -- |
| Build | Laravel Mix | ^6.0 |
| PDF Rendering | PDF.js | ^5.5 |
| Headless PDF | Puppeteer / Browsershot | ^24.39 |
| Testing | PHPUnit | ^9.5 |
| Database | MySQL / MariaDB | ^8.0 |

---

## Installation

### Prerequisites

- PHP >= 8.0
- Composer
- MySQL >= 8.0
- Node.js & NPM

### Step-by-Step

```bash
# Clone
git clone https://github.com/Rami-Saab/Store-Management.git
cd Store-Management

# PHP deps
composer install

# Node deps
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Storage link
php artisan storage:link

# Compile assets
npm run dev
```

---

## Database

### Core Tables

| Table | Purpose |
|-------|---------|
| `users` | Staff accounts with role pivots |
| `roles` / `permissions` | RBAC definitions |
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

`SearchStores` -- parameterized search across stores by name, province, status, and phone digits, returning paginated results with manager and province preloaded.

---

## API

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

---

## Security

### Role Hierarchy

| Role | Capabilities |
|------|--------------|
| `admin` | Full CRUD, assignments, product linking, brochure upload |
| `store_manager` | View assigned branches, limited edit, manage own staff |
| `store_employee` | View-only access to assigned branches |

### Defense Layers

1. **Middleware** -- `EnsureRole` validates role before route entry.
2. **Form Requests** -- `StoreRequest` / `UpdateStoreRequest` sanitize and validate all input.
3. **Policies** -- `StorePolicy` checks ownership, role scope, and state constraints before action.
4. **Transactions** -- `StoreCrudService` wraps mutations in DB transactions to prevent partial writes.
5. **Input Normalization** -- `EnglishInputNormalizer` and `UserContact::phone()` standardize text and phone data.

---

## Testing

```bash
# Run PHPUnit suite
php artisan test

# Feature tests cover
# - Store policy authorization
# - CRUD flows with validation
# - Assignment synchronization
# - Search result accuracy
```

---

## Screenshots

> _Screenshots will be added here to showcase the dashboard, store grid, detail view, assignment panel, and brochure viewer._

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
