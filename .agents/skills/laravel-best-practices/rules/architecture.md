# Architecture Best Practices

Best practices for Laravel application architecture, from simple to complex. Choose the right pattern for your application's size and complexity.

## Core Principle: Start Simple, Grow as Needed

Don't over-engineer. A Laravel app can grow through these stages:

1. **MVC** — Controllers + Models (for small apps)
2. **Action Classes** — Extract business logic (for medium apps)
3. **Modular Monolith** — Organize by domain (for large apps)
4. **Microservices** — Split into separate services (only when necessary)

Most Laravel apps should stay at stage 2 or 3. Microservices are rarely needed.

---

## 1. Single-Purpose Action Classes

Extract discrete business operations into invokable Action classes. Each action does ONE thing.

### DO ✅
```php
class CreateOrderAction
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly PaymentGateway $payment,
    ) {}

    public function execute(CreateOrderData $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create($data->toArray());
            
            $this->inventory->reserve($order);
            $this->payment->charge($order->total, $data->customerId);
            
            CreateOrderEvent::dispatch($order);
            
            return $order;
        });
    }
}

// Controller stays thin
class OrderController extends Controller
{
    public function store(
        StoreOrderRequest $request,
        CreateOrderAction $action,
    ): JsonResponse {
        $order = $action->execute(
            CreateOrderData::fromRequest($request)
        );
        
        return new OrderResource($order);
    }
}
```

### DON'T ❌
```php
// Fat controller with 80 lines of business logic
public function store(StoreOrderRequest $request)
{
    DB::transaction(function () use ($request) {
        $order = Order::create($request->validated());
        
        // Reserve inventory
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product->stock < $item->quantity) {
                throw new InsufficientStockException();
            }
            $product->decrement('stock', $item->quantity);
        }
        
        // Charge payment
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        $stripe->charges->create([...]);
        
        // Send email
        Mail::to($order->user)->send(new OrderConfirmation($order));
    });
}
```

### Action Class Guidelines

- **One action = one use case** (`CreateOrderAction`, `CancelOrderAction`, `RefundOrderAction`)
- **Inject dependencies via constructor** (services, gateways, repositories)
- **Accept DTOs or validated data**, not raw Request objects
- **Return domain objects** (Models, DTOs), not HTTP responses
- **Handle transactions inside the action**, not in controllers
- **Name with verb + noun**: `CreateOrder`, `SendInvoice`, `ProcessRefund`

---

## 2. Dependency Injection

Always use constructor injection. Avoid `app()` or `resolve()` inside classes.

### DO ✅
```php
class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $service,
    ) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        return $this->service->create($request->validated());
    }
}
```

### DON'T ❌
```php
class OrderController extends Controller
{
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $service = app(OrderService::class); // ❌ Service locator anti-pattern
        return $service->create($request->validated());
    }
}
```

### When `app()` is Acceptable

Only in these specific cases:
- **Service providers** (binding definitions)
- **Static helper methods** (when DI is impossible)
- **Legacy code** during migration (temporary)

---

## 3. Code to Interfaces at System Boundaries

Depend on contracts at system boundaries (payment gateways, notification channels, external APIs) for testability and swappability.

### DO ✅
```php
// Interface at system boundary
interface PaymentGateway
{
    public function charge(int $amount, string $customerId): PaymentResult;
    public function refund(string $transactionId): RefundResult;
}

// Implementation
class StripeGateway implements PaymentGateway
{
    public function __construct(
        private readonly StripeClient $client,
    ) {}

    public function charge(int $amount, string $customerId): PaymentResult
    {
        $charge = $this->client->charges->create([
            'amount' => $amount,
            'customer' => $customerId,
        ]);
        
        return PaymentResult::fromStripe($charge);
    }
}

// Service depends on interface
class OrderService
{
    public function __construct(
        private readonly PaymentGateway $gateway, // ✅ Interface
    ) {}
}

// Bind in service provider
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, StripeGateway::class);
    }
}
```

### DON'T ❌
```php
// Concrete dependency - hard to swap, hard to test
class OrderService
{
    public function __construct(
        private readonly StripeGateway $gateway, // ❌ Concrete class
    ) {}
}
```

### Where to Use Interfaces

| Use Interface | Don't Use Interface |
|---------------|---------------------|
| Payment gateways | Eloquent Models |
| Email/SMS providers | Internal services |
| External APIs | Repositories for simple CRUD |
| Storage drivers | Form Requests |
| Cache drivers | Actions |

---

## 4. Repository Pattern (Pragmatic Approach)

**Important**: Don't create repositories for Eloquent models just for the sake of it. Eloquent IS a repository.

### When to Use Repository Pattern ✅

1. **External APIs** — Wrap third-party services (Stripe, SendGrid, AWS)
2. **Complex data sources** — Combine multiple sources (DB + cache + API)
3. **Multi-database setups** — Abstract which database to query
4. **Strict DDD** — When following Domain-Driven Design strictly
5. **Testing** — When you need clean mocks without Eloquent

### When NOT to Use Repository Pattern ❌

1. **Simple CRUD** — Just use Eloquent directly
2. **Single database** — Eloquent already abstracts the DB
3. **Small teams** — Adds boilerplate without benefit
4. **Leveraging Eloquent features** — Scopes, relationships, eager loading

### Example: Repository for External API ✅

```php
// Interface
interface CustomerRepository
{
    public function findByEmail(string $email): ?CustomerData;
    public function create(CustomerData $data): CustomerData;
    public function update(string $id, CustomerData $data): CustomerData;
}

// Implementation wrapping external API
class HubSpotCustomerRepository implements CustomerRepository
{
    public function __construct(
        private readonly HubSpotClient $client,
    ) {}

    public function findByEmail(string $email): ?CustomerData
    {
        $response = $this->client->crm()->contacts()->search([
            'filterGroups' => [[
                'filters' => [['propertyName' => 'email', 'operator' => 'EQ', 'value' => $email]]
            ]]
        ]);
        
        if (empty($response->getResults())) {
            return null;
        }
        
        return CustomerData::fromHubSpot($response->getResults()[0]);
    }

    public function create(CustomerData $data): CustomerData
    {
        $response = $this->client->crm()->contacts()->create([
            'properties' => $data->toHubSpot(),
        ]);
        
        return CustomerData::fromHubSpot($response);
    }
}

// Action uses repository
class CreateCustomerAction
{
    public function __construct(
        private readonly CustomerRepository $customers,
    ) {}

    public function execute(CreateCustomerData $data): CustomerData
    {
        // Check if exists
        if ($existing = $this->customers->findByEmail($data->email)) {
            return $existing;
        }
        
        return $this->customers->create($data);
    }
}
```

### Example: Repository for Complex Queries ✅

```php
// When queries are complex and reused across multiple actions
interface OrderRepository
{
    public function findRevenueReport(
        Carbon $from,
        Carbon $to,
        array $filters = [],
    ): RevenueReport;
}

class EloquentOrderRepository implements OrderRepository
{
    public function findRevenueReport(
        Carbon $from,
        Carbon $to,
        array $filters = [],
    ): RevenueReport {
        $query = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', OrderStatus::Completed);
        
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        
        $data = $query->toBase()
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as avg_order_value,
                DATE(created_at) as date
            ')
            ->groupBy('date')
            ->get();
        
        return RevenueReport::fromCollection($data);
    }
}
```

### Example: DON'T Create Repository for Simple CRUD ❌

```php
// ❌ Unnecessary boilerplate for simple operations
interface UserRepository
{
    public function find(int $id): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function delete(User $user): void;
}

class EloquentUserRepository implements UserRepository
{
    public function find(int $id): ?User
    {
        return User::find($id); // Just wrapping Eloquent!
    }
    
    public function create(array $data): User
    {
        return User::create($data); // Pointless abstraction
    }
}

// ✅ Just use Eloquent directly
class CreateUserController
{
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());
        return new UserResource($user);
    }
}
```

### Repository Guidelines

- **Use interfaces** for all repositories (testability)
- **Return DTOs or domain objects**, not Eloquent models (when wrapping external APIs)
- **Don't expose Eloquent** — repository should hide implementation details
- **One repository per aggregate root** (not per table)
- **Inject repositories into Actions/Services**, not controllers
- **Bind in service provider** for easy swapping

---

## 5. Modular Architecture (Domain-Driven Design)

For large Laravel applications, organize code by **business domain** rather than technical layer.

### Traditional Laravel Structure (for small apps)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── OrderController.php
│   │   ├── ProductController.php
│   │   └── UserController.php
│   └── Requests/
├── Models/
│   ├── Order.php
│   ├── Product.php
│   └── User.php
├── Services/
└── Repositories/
```

**Problem**: As app grows, related code is scattered across folders. Hard to find all order-related code.

### Modular Structure (for large apps)

```
app/
├── Modules/
│   ├── Orders/
│   │   ├── Domain/
│   │   │   ├── Order.php (Entity)
│   │   │   ├── OrderStatus.php (Enum)
│   │   │   ├── OrderItem.php (Value Object)
│   │   │   ├── Events/
│   │   │   │   └── OrderCreated.php
│   │   │   └── Exceptions/
│   │   │       └── InsufficientStockException.php
│   │   ├── Application/
│   │   │   ├── Actions/
│   │   │   │   ├── CreateOrderAction.php
│   │   │   │   ├── CancelOrderAction.php
│   │   │   │   └── RefundOrderAction.php
│   │   │   ├── DTOs/
│   │   │   │   ├── CreateOrderData.php
│   │   │   │   └── OrderData.php
│   │   │   ├── Services/
│   │   │   │   └── OrderService.php
│   │   │   └── Contracts/
│   │   │       └── OrderRepository.php
│   │   ├── Infrastructure/
│   │   │   ├── Repositories/
│   │   │   │   └── EloquentOrderRepository.php
│   │   │   ├── ExternalApis/
│   │   │   │   └── StripePaymentGateway.php
│   │   │   └── Persistence/
│   │   │       └── OrderModel.php (Eloquent model)
│   │   ├── Presentation/
│   │   │   ├── Controllers/
│   │   │   │   └── OrderController.php
│   │   │   ├── Requests/
│   │   │   │   ├── StoreOrderRequest.php
│   │   │   │   └── UpdateOrderRequest.php
│   │   │   ├── Resources/
│   │   │   │   └── OrderResource.php
│   │   │   └── ViewModels/
│   │   │       └── OrderViewModel.php
│   │   ├── Database/
│   │   │   ├── Migrations/
│   │   │   ├── Factories/
│   │   │   └── Seeders/
│   │   ├── Routes/
│   │   │   ├── api.php
│   │   │   └── web.php
│   │   ├── Tests/
│   │   │   ├── Unit/
│   │   │   └── Feature/
│   │   └── OrderServiceProvider.php
│   ├── Products/
│   │   └── ... (same structure)
│   └── Customers/
│       └── ... (same structure)
├── Shared/
│   ├── Domain/
│   │   ├── ValueObjects/
│   │   │   ├── Money.php
│   │   │   └── Email.php
│   │   └── Events/
│   ├── Application/
│   │   └── Actions/
│   └── Infrastructure/
│       ├── Cache/
│       ├── Logging/
│       └── Mail/
└── Support/
    ├── Helpers/
    └── Macros/
```

### Module Layers Explained

#### Domain Layer (Core Business Logic)

The heart of your application. No Laravel dependencies.

```php
// app/Modules/Orders/Domain/Order.php
namespace App\Modules\Orders\Domain;

use App\Modules\Orders\Domain\Events\OrderCreated;
use App\Shared\Domain\ValueObjects\Money;
use Illuminate\Database\Eloquent\Collection;

class Order
{
    private function __construct(
        public readonly OrderId $id,
        public readonly CustomerId $customerId,
        public OrderStatus $status,
        public readonly Collection $items,
        public readonly Money $totalAmount,
        public readonly \DateTimeImmutable $createdAt,
    ) {}

    public static function create(
        CustomerId $customerId,
        array $items,
    ): self {
        $order = new self(
            id: OrderId::generate(),
            customerId: $customerId,
            status: OrderStatus::Pending,
            items: collect($items),
            totalAmount: self::calculateTotal($items),
            createdAt: new \DateTimeImmutable(),
        );
        
        // Domain event
        OrderCreated::dispatch($order);
        
        return $order;
    }

    public function cancel(): void
    {
        if (!$this->status->canBeCancelled()) {
            throw new CannotCancelOrderException($this->status);
        }
        
        $this->status = OrderStatus::Cancelled;
    }

    private static function calculateTotal(array $items): Money
    {
        return array_reduce(
            $items,
            fn (Money $sum, OrderItem $item) => $sum->add($item->subtotal()),
            Money::zero()
        );
    }
}
```

#### Application Layer (Use Cases)

Orchestrates domain objects and infrastructure.

```php
// app/Modules/Orders/Application/Actions/CreateOrderAction.php
namespace App\Modules\Orders\Application\Actions;

use App\Modules\Orders\Application\Contracts\OrderRepository;
use App\Modules\Orders\Application\DTOs\CreateOrderData;
use App\Modules\Orders\Domain\Order;
use App\Modules\Orders\Domain\Events\OrderCreated;
use App\Modules\Products\Application\Contracts\InventoryService;
use App\Shared\Application\Contracts\PaymentGateway;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly InventoryService $inventory,
        private readonly PaymentGateway $payment,
    ) {}

    public function execute(CreateOrderData $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            // Create domain entity
            $order = Order::create(
                customerId: $data->customerId,
                items: $data->items,
            );
            
            // Reserve inventory
            foreach ($order->items as $item) {
                $this->inventory->reserve($item->productId, $item->quantity);
            }
            
            // Charge payment
            $this->payment->charge($order->totalAmount, $data->paymentMethod);
            
            // Persist
            $this->orders->save($order);
            
            return $order;
        });
    }
}
```

#### Infrastructure Layer (Technical Details)

Implements interfaces defined in Application layer.

```php
// app/Modules/Orders/Infrastructure/Repositories/EloquentOrderRepository.php
namespace App\Modules\Orders\Infrastructure\Repositories;

use App\Modules\Orders\Application\Contracts\OrderRepository;
use App\Modules\Orders\Domain\Order;
use App\Modules\Orders\Infrastructure\Persistence\OrderModel;

class EloquentOrderRepository implements OrderRepository
{
    public function save(Order $order): void
    {
        $model = OrderModel::findOrNew($order->id->value);
        
        $model->fill([
            'customer_id' => $order->customerId->value,
            'status' => $order->status->value,
            'total_amount' => $order->totalAmount->amount(),
            'currency' => $order->totalAmount->currency(),
        ]);
        
        $model->save();
        
        // Sync items
        $model->items()->delete();
        foreach ($order->items as $item) {
            $model->items()->create([
                'product_id' => $item->productId->value,
                'quantity' => $item->quantity,
                'price' => $item->price->amount(),
            ]);
        }
    }

    public function findById(string $id): ?Order
    {
        $model = OrderModel::with('items')->find($id);
        
        return $model?->toDomain();
    }
}
```

#### Presentation Layer (HTTP/API)

Handles HTTP concerns only.

```php
// app/Modules/Orders/Presentation/Controllers/OrderController.php
namespace App\Modules\Orders\Presentation\Controllers;

use App\Modules\Orders\Application\Actions\CreateOrderAction;
use App\Modules\Orders\Application\DTOs\CreateOrderData;
use App\Modules\Orders\Presentation\Requests\StoreOrderRequest;
use App\Modules\Orders\Presentation\Resources\OrderResource;
use Illuminate\Http\JsonResponse;

class OrderController
{
    public function store(
        StoreOrderRequest $request,
        CreateOrderAction $action,
    ): JsonResponse {
        $order = $action->execute(
            CreateOrderData::fromRequest($request)
        );
        
        return new OrderResource($order);
    }
}
```

### Module Service Provider

Each module has its own service provider for bindings.

```php
// app/Modules/Orders/OrderServiceProvider.php
namespace App\Modules\Orders;

use App\Modules\Orders\Application\Contracts\OrderRepository;
use App\Modules\Orders\Infrastructure\Repositories\EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
    }

    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        
        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/Resources/lang', 'orders');
    }
}
```

### Module Routes

```php
// app/Modules/Orders/Routes/api.php
use App\Modules\Orders\Presentation\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('api/v1')->group(function () {
    Route::apiResource('orders', OrderController::class);
    
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('orders/{order}/refund', [OrderController::class, 'refund']);
});
```

---

## 6. Default Sort by Descending

When no explicit order is specified, sort by `id` or `created_at` descending. Without an explicit `ORDER BY`, row order is undefined.

### DO ✅
```php
$posts = Post::latest()->paginate();
```

### DON'T ❌
```php
$posts = Post::paginate(); // ❌ Undefined order
```

---

## 7. Use Atomic Locks for Race Conditions

Prevent race conditions with `Cache::lock()` or `lockForUpdate()`.

### DO ✅
```php
// Application-level lock
Cache::lock('order-processing-'.$order->id, 10)->block(5, function () use ($order) {
    $order->process();
});

// Database-level lock (pessimistic locking)
$product = Product::where('id', $id)->lockForUpdate()->first();
$product->decrement('stock', $quantity);
```

### When to Use Each

| Use Case | Lock Type |
|----------|-----------|
| Prevent duplicate job execution | `Cache::lock()` |
| Prevent concurrent updates to same row | `lockForUpdate()` |
| Rate limiting | `Cache::lock()` with TTL |
| Distributed systems | `Cache::lock()` with Redis |

---

## 8. Use `mb_*` String Functions

When no Laravel helper exists, prefer `mb_strlen`, `mb_strtolower`, etc. for UTF-8 safety.

### DO ✅
```php
// Prefer Laravel's Str helpers
Str::length('José');          // 4
Str::lower('MÜNCHEN');        // 'münchen'

// Or mb_* functions
mb_strlen('José');            // 4
mb_strtolower('MÜNCHEN');     // 'münchen'
```

### DON'T ❌
```php
strlen('José');               // 5 (bytes, not characters)
strtolower('MÜNCHEN');        // 'mÜnchen' — fails on multibyte
```

---

## 9. Use `defer()` for Post-Response Work

For lightweight tasks that don't need to survive a crash (logging, analytics, cleanup), use `defer()` instead of dispatching a job.

### DO ✅
```php
// Runs after response, same process — no queue overhead
defer(fn () => PageView::create([
    'page_id' => $page->id,
    'user_id' => auth()->id(),
]));
```

### DON'T ❌
```php
// Job overhead for trivial work
dispatch(new LogPageView($page));
```

### When to Use Jobs vs defer()

| Use `defer()` | Use Jobs |
|---------------|----------|
| Logging, analytics | Payment processing |
| Cleanup tasks | Email sending |
| Cache warming | Image processing |
| Metrics collection | Long-running tasks |
| | Need retry logic |
| | Must survive crashes |

---

## 10. Use `Context` for Request-Scoped Data

The `Context` facade passes data through the entire request lifecycle without passing arguments manually.

### DO ✅
```php
// In middleware
Context::add('tenant_id', $request->header('X-Tenant-ID'));
Context::add('request_id', Str::uuid());

// Anywhere later — controllers, actions, jobs
$tenantId = Context::get('tenant_id');

// In logging
Log::info('Order created', ['order_id' => $order->id]);
// Automatically includes context: tenant_id, request_id
```

### Use `addHidden()` for Sensitive Data

```php
// Available in queued jobs, but NOT in logs
Context::addHidden('api_key', $request->header('X-API-Key'));
```

---

## 11. Use `Concurrency::run()` for Parallel Execution

Run independent operations in parallel using child processes.

### DO ✅
```php
use Illuminate\Support\Facades\Concurrency;

[$users, $orders, $revenue] = Concurrency::run([
    fn () => User::count(),
    fn () => Order::where('status', 'pending')->count(),
    fn () => Order::where('status', 'completed')->sum('total'),
]);
```

### When to Use

- Independent database queries
- Multiple API calls to different services
- Parallel computations

### Don't Use When

- Operations depend on each other
- You need shared state between closures
- Simple sequential code is fast enough

---

## 12. Convention Over Configuration

Follow Laravel conventions. Don't override defaults unnecessarily.

### DO ✅
```php
class Customer extends Model
{
    // Uses 'customers' table automatically
    // Uses 'id' as primary key automatically
    
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
        // Uses 'customer_role' pivot table automatically
    }
}
```

### DON'T ❌
```php
class Customer extends Model
{
    protected $table = 'Customer'; // ❌ Non-standard
    protected $primaryKey = 'customer_id'; // ❌ Non-standard

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_customer', // ❌ Non-standard pivot table name
            'customer_id',   // ❌ Non-standard foreign key
            'role_id'
        );
    }
}
```

---

## 13. Choosing the Right Architecture

### Small App (< 10k lines of code)

```
MVC + Action Classes
- Controllers handle HTTP
- Actions handle business logic
- Eloquent models handle data
- No repositories needed
```

### Medium App (10k - 50k lines)

```
MVC + Action Classes + Services
- Organize by feature (folders: Orders, Products, Users)
- Use services for shared logic
- Repositories only for external APIs
- DTOs for complex data transfer
```

### Large App (50k+ lines)

```
Modular Monolith (DDD-inspired)
- Each module is self-contained
- Domain layer has no Laravel dependencies
- Application layer orchestrates use cases
- Infrastructure layer implements interfaces
- Presentation layer handles HTTP
- Shared kernel for cross-cutting concerns
```

### Microservices (Only When Necessary)

Only consider microservices when:
- ✅ Team is too large for single codebase (> 20 developers)
- ✅ Different parts need different tech stacks
- ✅ Parts need to scale independently
- ✅ You have mature DevOps infrastructure

**Most Laravel apps should NOT use microservices.**

---

## Summary: Architecture Decision Tree

```
Starting a new Laravel project?
│
├─ Small app? (< 10k lines)
│  └─ Use MVC + Action Classes
│
├─ Medium app? (10k - 50k lines)
│  ├─ Organize by feature
│  ├─ Use Action Classes for business logic
│  ├─ Use Services for shared logic
│  └─ Repositories only for external APIs
│
└─ Large app? (50k+ lines)
   ├─ Use Modular Monolith (DDD-inspired)
   ├─ Each module is self-contained
   ├─ Domain layer is framework-agnostic
   ├─ Application layer orchestrates use cases
   ├─ Infrastructure implements interfaces
   └─ Presentation handles HTTP
```

---

## Anti-Patterns to Avoid

### 1. God Controller
```php
// ❌ 500 lines, does everything
class OrderController extends Controller
{
    public function store() { /* 100 lines */ }
    public function update() { /* 80 lines */ }
    public function cancel() { /* 60 lines */ }
    // ... 20 more methods
}
```

### 2. Fat Model
```php
// ❌ Model with business logic, API calls, email sending
class Order extends Model
{
    public function process()
    {
        // 200 lines of business logic
        // API calls to Stripe
        // Sending emails
        // Updating inventory
    }
}
```

### 3. Anemic Domain Model
```php
// ❌ Domain objects with only getters/setters
class Order
{
    public function getStatus(): OrderStatus { return $this->status; }
    public function setStatus(OrderStatus $status): void { $this->status = $status; }
    // No business logic!
}
```

### 4. Repository for Everything
```php
// ❌ Repository wrapping Eloquent for simple CRUD
interface UserRepository {
    public function find(int $id): ?User;
    public function create(array $data): User;
}
```

### 5. Service Locator
```php
// ❌ Using app() everywhere
class OrderService
{
    public function create(array $data)
    {
        $gateway = app(PaymentGateway::class);
        $repo = app(OrderRepository::class);
        // ...
    }
}
```