# Collection Best Practices

Best practices for Laravel Collections, focusing on performance, readability, and choosing the right tool for the job.

## Core Principle: Collection vs Query Builder

**Rule of thumb:** Do as much as possible in the database (Query Builder), then use Collections for in-memory operations.

### Decision Tree

```
Need to process data?
│
├─ Data already in memory (array/Collection)?
│  └─ Use Collection methods
│
├─ Data in database?
│  ├─ Small dataset (< 1000 records)?
│  │  └─ Query Builder → get() → Collection methods
│  │
│  ├─ Large dataset (1000 - 100k records)?
│  │  ├─ Need relationships?
│  │  │  └─ lazy() with eager loading
│  │  │
│  │  └─ No relationships?
│  │     └─ cursor() for memory efficiency
│  │
│  └─ Very large dataset (100k+ records)?
│     └─ cursor() or chunk() with batch processing
│
└─ Need to transform/aggregate?
   ├─ Can do in SQL?
   │  └─ Use Query Builder (selectRaw, groupBy, etc.)
   │
   └─ Complex logic?
      └─ Use Collection methods (map, reduce, etc.)
```

---

## 1. Use Higher-Order Messages for Simple Operations

Higher-order messages provide a concise syntax for common operations on collections.

### DO ✅
```php
// ✅ Simple method calls
$users->each->markAsVip();
$users->each->sendWelcomeEmail();

// ✅ Property access
$names = $users->map->name;
$emails = $users->pluck('email');

// ✅ With filtering
$activeUsers = $users->filter->isActive();
$inactiveUsers = $users->reject->isActive();

// ✅ Aggregations
$totalBalance = $users->sum->balance;
$hasAdmin = $users->contains->isAdmin();

// ✅ Chaining
$users->filter->isActive()
      ->each->sendNewsletter();
```

### DON'T ❌
```php
// ❌ Verbose for simple operations
$users->each(function (User $user) {
    $user->markAsVip();
});

// ❌ Unnecessary closure
$names = $users->map(function ($user) {
    return $user->name;
});

// ❌ Manual filtering
$activeUsers = $users->filter(function ($user) {
    return $user->isActive();
});
```

### When NOT to Use Higher-Order Messages

```php
// ❌ Complex logic - use explicit closure
$users->filter(function ($user) {
    return $user->isActive() 
        && $user->hasSubscription()
        && $user->created_at->gt(now()->subMonth());
});

// ✅ Simple condition - higher-order message
$users->filter->isActive();
```

### Available Higher-Order Methods

```php
// Iteration
$collection->each->method();
$collection->map->property;
$collection->flatMap->method();

// Filtering
$collection->filter->method();
$collection->reject->method();
$collection->contains->method();
$collection->every->method();

// Aggregation
$collection->sum->property;
$collection->avg->property;
$collection->min->property;
$collection->max->property;

// Search
$collection->first->method();
$collection->last->method();
```

---

## 2. Choose `cursor()` vs. `lazy()` Correctly

Understanding the difference is crucial for performance and correctness.

### `cursor()` — Memory Efficient, No Relationships

```php
// ✅ Use when: Processing many records, no relationship access needed
User::cursor()->each(function ($user) {
    // Only access $user attributes, NOT relationships
    echo $user->name;
    echo $user->email;
    
    // ❌ This will trigger N+1 query!
    // echo $user->roles->count();
});

// Memory usage: ~1 model at a time
// Speed: Very fast, single query
```

### `lazy()` — Chunked, Supports Relationships

```php
// ✅ Use when: Need to access relationships
User::with('roles')->lazy()->each(function ($user) {
    echo $user->name;
    echo $user->roles->count(); // ✅ No N+1, eager loaded
});

// Memory usage: ~1000 models at a time (configurable)
// Speed: Fast, chunked queries
```

### Common Mistakes

```php
// ❌ Eager loading silently ignored with cursor()
$users = User::with('roles')->cursor();
foreach ($users as $user) {
    $user->roles; // ❌ Triggers N+1 query!
}

// ✅ Use lazy() for relationships
$users = User::with('roles')->lazy();
foreach ($users as $user) {
    $user->roles; // ✅ Eager loaded
}

// ❌ cursor() with relationship access in loop
User::cursor()->each(function ($user) {
    $user->load('roles'); // ❌ N+1 query!
});

// ✅ lazy() with eager loading
User::with('roles')->lazy()->each(function ($user) {
    // Roles already loaded
});
```

### Performance Comparison

```php
// Scenario: Process 10,000 users with their roles

// ❌ cursor() + lazy load = 10,001 queries
User::cursor()->each(function ($user) {
    $user->roles->count(); // Query per user
});

// ✅ lazy() + eager load = 11 queries (1 for users, 10 for roles chunks)
User::with('roles')->lazy(1000)->each(function ($user) {
    $user->roles->count(); // No query
});

// ✅ cursor() without relationships = 1 query
User::cursor()->each(function ($user) {
    echo $user->name; // No query
});
```

### Decision Guide

| Scenario | Use |
|----------|-----|
| Process 100k users, no relationships | `cursor()` |
| Process 10k users with roles | `lazy()` with `with('roles')` |
| Update records while iterating | `lazyById()` |
| Export to CSV (memory efficient) | `cursor()` |
| Send emails to users | `lazy()` with chunking |

---

## 3. Use `lazyById()` When Updating Records While Iterating

`lazy()` uses offset-based pagination (`LIMIT offset, count`), which breaks when records are updated/deleted during iteration.

### The Problem with `lazy()`

```php
// ❌ Records get skipped or double-processed
User::lazy(100)->each(function ($user) {
    if ($user->shouldDelete()) {
        $user->delete(); // ❌ Next chunk skips records!
    }
});

// Why? 
// Chunk 1: IDs 1-100, delete ID 50
// Chunk 2: LIMIT 100 OFFSET 100 → gets IDs 101-200 (skipped ID 51-100 shifted)
```

### The Solution: `lazyById()`

```php
// ✅ Safe for updates/deletes
User::lazyById(100)->each(function ($user) {
    if ($user->shouldDelete()) {
        $user->delete(); // ✅ Safe, uses WHERE id > last_id
    }
    
    $user->update(['processed' => true]); // ✅ Safe
});

// How it works:
// Chunk 1: WHERE id > 0 ORDER BY id LIMIT 100 → IDs 1-100
// Chunk 2: WHERE id > 100 ORDER BY id LIMIT 100 → IDs 101-200
// No skipping, no duplicates
```

### When to Use Each

```php
// ✅ lazy() - Read-only operations
User::lazy()->each(function ($user) {
    $this->exportUser($user); // Read-only
});

// ✅ lazyById() - Updates/deletes
User::lazyById()->each(function ($user) {
    $user->update(['status' => 'processed']);
});

// ✅ lazyByIdDesc() - Process newest first
User::lazyByIdDesc()->each(function ($user) {
    $user->archive();
});
```

---

## 4. Use `toQuery()` for Bulk Operations on Collections

Convert a collection back to a query builder for efficient bulk operations.

### DO ✅
```php
// ✅ Convert collection to query for bulk update
$users = User::where('status', 'pending')->get();
$users->toQuery()->update(['status' => 'processed']);

// ✅ Bulk delete
$oldPosts = Post::where('created_at', '<', now()->subYear())->get();
$oldPosts->toQuery()->delete();

// ✅ Bulk operations with conditions
$inactiveUsers = User::where('last_login', '<', now()->subMonth())->get();
$inactiveUsers->toQuery()->update(['status' => 'inactive']);
```

### DON'T ❌
```php
// ❌ Manual whereIn construction
$users = User::where('status', 'pending')->get();
User::whereIn('id', $users->pluck('id'))->update(['status' => 'processed']);

// ❌ Loop updates (N queries)
$users = User::where('status', 'pending')->get();
$users->each(function ($user) {
    $user->update(['status' => 'processed']); // Query per user!
});
```

### Advanced Usage

```php
// ✅ Complex bulk operations
$orders = Order::where('status', 'pending')
    ->where('created_at', '<', now()->subDays(30))
    ->get();

$orders->toQuery()->update([
    'status' => 'cancelled',
    'cancelled_at' => now(),
    'cancellation_reason' => 'Expired',
]);

// ✅ With relationships
$posts = Post::with('comments')->where('draft', true)->get();
$posts->toQuery()->delete(); // Deletes posts, comments handled by cascade
```

---

## 5. Use `#[CollectedBy]` for Custom Collection Classes

Laravel 11+ provides a cleaner way to specify custom collection classes.

### DO ✅
```php
// ✅ Declarative with attribute (Laravel 11+)
use Illuminate\Database\Eloquent\Attributes\CollectedBy;

#[CollectedBy(UserCollection::class)]
class User extends Model
{
    // ...
}

// Custom collection class
class UserCollection extends Collection
{
    public function active(): self
    {
        return $this->filter->isActive();
    }
    
    public function admins(): self
    {
        return $this->filter->isAdmin();
    }
    
    public function sortByLastLogin(): self
    {
        return $this->sortByDesc('last_login_at');
    }
}

// Usage
$users = User::all(); // Returns UserCollection
$activeUsers = $users->active();
$admins = $users->admins();
```

### Legacy Approach (Pre-Laravel 11)

```php
// ❌ Override newCollection() in model
class User extends Model
{
    public function newCollection(array $models = []): UserCollection
    {
        return new UserCollection($models);
    }
}
```

### When to Use Custom Collections

```php
// ✅ Domain-specific operations
class OrderCollection extends Collection
{
    public function pending(): self
    {
        return $this->where('status', OrderStatus::Pending);
    }
    
    public function totalRevenue(): float
    {
        return $this->sum('total_amount');
    }
    
    public function withDiscount(): self
    {
        return $this->filter(fn ($order) => $order->discount > 0);
    }
}

// ✅ Reusable across multiple queries
$orders = Order::all();
$revenue = $orders->totalRevenue();
$pendingOrders = $orders->pending();
```

---

## 6. Collection Macros for Reusable Operations

Extend Collection functionality with custom methods.

### DO ✅
```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Collection;

public function boot(): void
{
    // Simple macro
    Collection::macro('toCsv', function () {
        $headers = array_keys($this->first()->toArray());
        $rows = $this->map(fn ($item) => array_values($item->toArray()));
        
        return collect([$headers])->merge($rows)
            ->map(fn ($row) => implode(',', $row))
            ->implode("\n");
    });
    
    // Complex macro
    Collection::macro('groupByDate', function (string $column, string $format = 'Y-m-d') {
        return $this->groupBy(function ($item) use ($column, $format) {
            return $item->{$column}->format($format);
        });
    });
}

// Usage
$users = User::all();
$csv = $users->toCsv();

$posts = Post::all();
$grouped = $posts->groupByDate('created_at', 'Y-m');
```

### Advanced Macros

```php
// Macro with parameters
Collection::macro('paginate', function (int $perPage = 15, int $page = null) {
    $page = $page ?: Paginator::resolveCurrentPage();
    
    return new LengthAwarePaginator(
            $this->forPage($page, $perPage),
            $this->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );
});

// Usage
$users = User::all()->paginate(20, 2); // 20 per page, page 2
```

---

## 7. Common Collection Patterns

### Grouping and Aggregation

```php
// ✅ Group by relationship
$posts = Post::with('author')->get();
$postsByAuthor = $posts->groupBy('author_id');

// ✅ Group by computed value
$users = User::all();
$usersByAge = $users->groupBy(function ($user) {
    return $user->age < 18 ? 'minor' : 'adult';
});

// ✅ Multi-level grouping
$orders = Order::with(['user', 'products'])->get();
$grouped = $orders->groupBy('user_id')
    ->map(fn ($userOrders) => $userOrders->groupBy('status'));
```

### Chunking for Batch Processing

```php
// ✅ Process in batches
$users = User::all();
$users->chunk(100)->each(function ($chunk) {
    // Send emails in batches of 100
    Mail::to($chunk)->send(new Newsletter());
});

// ✅ Chunk with callback
User::all()->chunk(100, function ($users) {
    foreach ($users as $user) {
        $user->process();
    }
});
```

### Combining Collections

```php
// ✅ Merge collections
$activeUsers = User::where('status', 'active')->get();
$vipUsers = User::where('is_vip', true)->get();
$allUsers = $activeUsers->merge($vipUsers)->unique('id');

// ✅ Combine with keys
$keys = collect(['name', 'email', 'phone']);
$values = collect(['John', 'john@example.com', '123-456-7890']);
$user = $keys->combine($values); // ['name' => 'John', 'email' => 'john@example.com', ...]

// ✅ Cross join
$sizes = collect(['S', 'M', 'L']);
$colors = collect(['Red', 'Blue']);
$variants = $sizes->crossJoin($colors);
// [['S', 'Red'], ['S', 'Blue'], ['M', 'Red'], ...]
```

### Transformations

```php
// ✅ Map to key-value pairs
$users = User::all();
$userMap = $users->mapWithKeys(fn ($user) => [$user->id => $user->name]);

// ✅ Map to dictionary
$usersById = $users->keyBy('id');
$usersByEmail = $users->keyBy('email');

// ✅ Pluck nested values
$posts = Post::with('author')->get();
$authorNames = $posts->pluck('author.name'); // ['John', 'Jane', ...]

// ✅ Map multiple fields
$users->map(function ($user) {
    return [
        'id' => $user->id,
        'full_name' => "{$user->first_name} {$user->last_name}",
        'email' => $user->email,
    ];
});
```

---

## 8. Performance Considerations

### Collection vs Query Builder

```php
// ❌ Load all then filter in PHP (slow for large datasets)
$users = User::all()->filter(fn ($u) => $u->age > 18);

// ✅ Filter in database (fast)
$users = User::where('age', '>', 18)->get();

// ❌ Load all then sort in PHP
$users = User::all()->sortBy('created_at');

// ✅ Sort in database
$users = User::orderBy('created_at')->get();
```

### Memory Efficiency

```php
// ❌ Load entire collection into memory
$users = User::all(); // 100k users = high memory usage

// ✅ Use cursor for large datasets
User::cursor()->each(function ($user) {
    // Only 1 user in memory at a time
});

// ✅ Use lazy for chunked processing
User::lazy(1000)->each(function ($user) {
    // 1000 users in memory at a time
});
```

### Avoid Unnecessary Operations

```php
// ❌ Multiple passes
$users = User::all();
$active = $users->filter->isActive();
$sorted = $active->sortBy('name');
$names = $sorted->pluck('name');

// ✅ Chain operations (single pass)
$names = User::all()
    ->filter->isActive()
    ->sortBy('name')
    ->pluck('name');

// ❌ Redundant operations
$users = User::all()->sortBy('name')->sortBy('email'); // Second sort overrides first

// ✅ Sort by multiple columns
$users = User::all()->sort(function ($a, $b) {
    return $a->name <=> $b->name ?: $a->email <=> $b->email;
});
```

---

## 9. Testing Collections

### DO ✅
```php
test('collection filters active users correctly', function () {
    $users = collect([
        User::factory()->make(['status' => 'active']),
        User::factory()->make(['status' => 'inactive']),
        User::factory()->make(['status' => 'active']),
    ]);
    
    $active = $users->filter->isActive();
    
    expect($active)->toHaveCount(2);
    expect($active->every->isActive())->toBeTrue();
});

test('custom collection method works', function () {
    $orders = new OrderCollection([
        Order::factory()->make(['status' => 'pending']),
        Order::factory()->make(['status' => 'completed']),
    ]);
    
    $pending = $orders->pending();
    
    expect($pending)->toHaveCount(1);
    expect($pending->first()->status)->toBe('pending');
});
```

---

## 10. LazyCollection for Generators

Use LazyCollection for memory-efficient processing of large datasets or generators.

### DO ✅
```php
// ✅ Process large file line by line
LazyCollection::make(function () {
    $handle = fopen('large-file.csv', 'r');
    while (($line = fgetcsv($handle)) !== false) {
        yield $line;
    }
})->each(function ($line) {
    // Process each line, only 1 in memory
    processLine($line);
});

// ✅ Infinite sequences
LazyCollection::times(100)->each(function ($number) {
    echo $number; // 1 to 100
});

// ✅ Chunk lazy collections
LazyCollection::make(function () {
    for ($i = 0; $i < 10000; $i++) {
        yield $i;
    }
})->chunk(100)->each(function ($chunk) {
    // Process 100 items at a time
});
```

---

## Summary: Collection Checklist

Before using collections:

- [ ] Can this be done in Query Builder instead? (faster)
- [ ] Using `cursor()` for large datasets without relationships?
- [ ] Using `lazy()` when relationships are needed?
- [ ] Using `lazyById()` when updating/deleting during iteration?
- [ ] Using `toQuery()` for bulk operations?
- [ ] Using higher-order messages for simple operations?
- [ ] Custom collection class for domain-specific operations?
- [ ] Memory usage considered for large datasets?
- [ ] No N+1 queries in collection loops?

---

## Anti-Patterns to Avoid

### 1. Loading Everything Then Filtering
```php
// ❌ Load 100k records, filter in PHP
$users = User::all()->filter(fn ($u) => $u->age > 18);

// ✅ Filter in database
$users = User::where('age', '>', 18)->get();
```

### 2. N+1 in Collection Loop
```php
// ❌ N+1 query
$users = User::all();
$users->each(function ($user) {
    echo $user->posts->count(); // Query per user!
});

// ✅ Eager load
$users = User::withCount('posts')->get();
$users->each(function ($user) {
    echo $user->posts_count; // No query
});
```

### 3. Modifying Collection During Iteration
```php
// ❌ Unpredictable behavior
$users = User::all();
$users->each(function ($user) use (&$users) {
    if ($user->shouldRemove()) {
        $users = $users->reject(fn ($u) => $u->id === $user->id);
    }
});

// ✅ Filter first, then iterate
$users = User::all()
    ->reject->shouldRemove()
    ->each->process();
```

### 4. Using Collections for Database Aggregations
```php
// ❌ Load all records, aggregate in PHP
$orders = Order::all();
$total = $orders->sum('amount');

// ✅ Aggregate in database
$total = Order::sum('amount');
```

### 5. Ignoring Memory Limits
```php
// ❌ Load 1M records into memory
$users = User::all(); // Out of memory!

// ✅ Use cursor or lazy
User::cursor()->each->process();
User::lazy()->each->process();
```