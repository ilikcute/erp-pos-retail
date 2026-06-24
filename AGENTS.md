# AGENTS.md — ERP POS, Inventory & Accounting Integration

## Project Overview

**Laravel Modular Layered Monolith** for integrated POS, Inventory, Purchasing, and Accounting.

- Backend: Laravel 13, PHP 8.3
- Frontend: Vue 3 + Inertia.js
- Database: MySQL 8
- Realtime: Laravel Reverb (WebSocket)
- Queue: Laravel Horizon

## Critical Architectural Constraints

### 12 Mandatory Modules

Do **not** create new modules outside these 12:

```
Auth, System, MasterData, Product, Pricing, Promotion, POS, Inventory, Purchasing, Loyalty, Accounting, Reporting
```

Module boundaries are strict. Misplaced code creates tech debt fast in a monolith.

### Strict Layering (Controller → Request → Action → Service → Repository → Model)

**Never violate this order:**

1. **Controller**: HTTP routing only; no business logic
2. **Form Request**: Input validation + authorization checks
3. **Action**: Orchestrates business transactions (posting, approval flows); must use DB transactions
4. **Service**: Reusable domain logic; callable from Actions or other Services
5. **Repository**: Data access via contracts + Eloquent implementations
6. **Model**: Relations and attribute casting only; never embed posting logic

**Vet any cross-layer shortcuts.** Controllers querying models directly, or Services creating HTTP responses = red flag.

## Code Organization

```
app/
├── Actions/{Module}/          # Business transactions & posting flows
├── Services/{Module}/         # Domain logic
├── Models/{Module}/           # Eloquent models
├── Http/
│   ├── Controllers/Api/{Module}/
│   ├── Requests/{Module}/     # Form requests
│   └── Resources/{Module}/    # API response formats
├── Repositories/
│   ├── Contracts/{Module}/    # Interfaces
│   └── Eloquent/{Module}/     # Implementations
├── Events/                    # Business events (SalesTransactionPosted, etc.)
├── Listeners/                 # Non-critical side effects (notifications, audit logs)
├── Jobs/                      # Async tasks (exports, report generation)
├── Enums/                     # Status, transaction types, payment methods
├── Policies/                  # Authorization per model
└── Support/                   # Helpers (NumberGenerator, Formatter, Calculator)
```

## Routes Structure

Each module has its own route file. Main entry point: `routes/api.php` → `routes/{module}.php`

```
routes/
├── api.php             # Version prefix, middleware grouping
├── auth.php            # Login/logout/password (mixed public + auth)
├── system.php          # Users, roles, permissions, audit
├── master-data.php     # Suppliers, customers, units, tax
├── product.php         # Products, brands, categories
├── pricing.php         # Price lists, price items
├── promotion.php       # Promotions, conditions, rewards
├── pos.php             # Shifts, sales, sessions, closing
├── inventory.php       # Stock movements, transfers, opname
├── purchasing.php      # PO, goods receipt, invoices
├── loyalty.php         # Loyalty accounts, transactions
├── accounting.php      # Chart of accounts, journals
└── reporting.php       # Read-only reports
```

## Database & Posting

### Immutability Rule

- **POSTED** documents cannot be edited directly
- Corrections: use VOID, CANCEL, RETURN, ADJUSTMENT, or REVERSAL JOURNAL
- Inventory Ledger = source of truth for stock (Balance is read cache)
- Journal Entry = source of truth for accounting (GL is derived)

### Transaction Posting (Always in Actions)

All posting logic goes in Action classes with database transactions. Examples:

```
CreateSalesTransactionAction
PostGoodsReceiptAction
PostSupplierInvoiceAction
PostStockTransferAction
PostJournalEntryAction
```

Key rule: **One business event → one Action → one DB transaction → atomic all-or-nothing**

## Command & Script Reference

### Development

```bash
npm run dev              # Frontend dev server (Vite) + Laravel serve concurrently
composer run dev         # Full stack: Laravel, queue, logs, Vite (see composer.json)
```

### Testing

```bash
composer run test        # Run all tests (PHPUnit)
php artisan test         # Alias for tests
php artisan test --filter=ApiAuthTest    # Run single test class
```

Test structure:

- Base class: `tests/ApiTestCase` for API tests (with helpers like `actingAsUser()`)
- Base class: `tests/TestCase` for other tests
- Coverage included in `phpunit.xml`

### Code Quality

```bash
php artisan pint         # Format code (Laravel's Pint)
```

**No dedicated lint/typecheck commands configured yet.** Teams using Pint for formatting should enforce it before commits.

## Key Model & Enum Patterns

### Model Naming

Models use business domain names; tables can differ via `protected $table`:

```php
// Model: InventoryLocation, Table: warehouse_locations
// Model: SalesHold (not "HoldBill" in code, only UI)
// Model: SupplierInvoice (not "PurchaseInvoice")
```

### Enums & Status

Document statuses follow standard states:

```
DRAFT → PENDING → APPROVED → POSTED → (VOID|CANCELLED|CLOSED)
```

Store in `app/Enums/` with same name as domain (e.g., `DocumentStatus`, `PaymentMethodType`, `InventoryMovementType`).

## API Conventions

- **Versioning**: All endpoints prefixed `/api/v1`
- **Response format**: Standard wrapper with `success`, `data`, `message`, `meta` (pagination)
- **Error handling**: Use consistent error codes and HTTP status
- **Pagination**: Default 15 items per page
- **Filters/Search**: Query params; exact patterns per module documented in `docs/API_DOCUMENTATION.md`

## Database Transactions & Posting

- **All posting logic** (sales, goods receipt, journal creation) = wrapped in `DB::transaction()`
- **Batch operations** in inventory or accounting = separate atomic transactions if safe, otherwise wrap all
- **Rollback** on validation failure or business rule violation = automatic via exception handling
- **Event dispatch** (SalesTransactionPosted, etc.) = after transaction commits (use `afterCommit()`)

## Realtime & Async

- **Events + Listeners** for notifications, audit logs, broadcast (non-critical tasks)
- **Jobs + Horizon** for export reports, snapshot generation, async notifications
- **Laravel Reverb** broadcasts transaction events to connected clients (stock update, new sale, etc.)

## Repository Pattern Rules

- All queries → repository interface (in `Contracts/`)
- Eloquent implementation in `Eloquent/` folder
- Bind in `RepositoryServiceProvider` (already configured)
- Complex reports → repository methods, not raw queries in controllers

## Testing Expectations

- **Tests run against SQLite** in-memory DB (see `phpunit.xml` `DB_CONNECTION=sqlite`)
- **Each Feature test** imports trait/base + sets up auth via `actingAsUser(role)`
- **API tests** use `postJson()`, `getJson()`, assert response structure
- **No external API calls** in tests; mock third-party services

## Common Gotchas

1. **Don't bypass layers**: No `Model::create()` in controllers; use Action
2. **Transactions matter**: Posting failures must rollback atomically
3. **Listeners are fire-and-forget**: Put retry logic in Jobs, not Listeners
4. **Immutability**: POSTED docs can't be modified; use reversal patterns
5. **Module isolation**: Keep POS stok flows separate from Inventory adjustments (use events)
6. **Enum status**: Always use Enum, not string, for document statuses
7. **Repository first**: Never query models directly from controllers; use injected repository

## Where to Find Standards

- **Full BRD**: `README.md` (12 sections covering architecture, business flows, database, API, backend, frontend, security, testing, deployment, phases, seeders, operations)
- **API endpoints**: `docs/API_DOCUMENTATION.md`
- **Database schema**: `docs/DATABASE.sql`
- **Example tests**: `tests/Feature/Api/{Module}/`
- **Example controllers**: `app/Http/Controllers/Api/{Module}/`

setiap jawaban di tambahkan emoticon, karena saya suka emoticon
