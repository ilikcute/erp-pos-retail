# Caching Best Practices

Best practices for Laravel caching with **file** and **array (memory)** cache stores. Optimized for applications without Redis/Memcached.

## Core Principle: Choose the Right Cache Store

### Cache Store Comparison

| Store | Persistence | Speed | Cross-Request | Use Case |
|-------|-------------|-------|---------------|----------|
| **Array** | ❌ No | ⚡⚡⚡ Fastest | ❌ No | Per-request memoization |
| **File** | ✅ Yes | 🐢 Slow | ✅ Yes | Small apps, shared hosting |
| **Database** | ✅ Yes | 🐢 Slow | ✅ Yes | Fallback when no Redis |
| **Redis** | ✅ Yes | ⚡⚡ Fast | ✅ Yes | Production apps (if available) |

### Decision Tree

```
Need to cache data?
│
├─ Only needed within single request?
│  └─ Use array cache or once()
│
├─ Needed across multiple requests?
│  ├─ Have Redis available?
│  │  └─ Use Redis (best performance)
│  │
│  └─ No Redis?
│     ├─ High traffic (> 1000 req/min)?
│     │  └─ Consider Redis (file cache will be bottleneck)
│     │
│     └─ Low traffic?
│        └─ Use file cache
│
└─ Need cache tags or atomic operations?
   └─ Must use Redis/Memcached
```

---

## 1. Use `Cache::remember()` Instead of Manual Get/Put

Cleaner cache-aside pattern that removes boilerplate.

### DO ✅
```php
// Clean and simple
$stats = Cache::remember('stats', 3600, fn () => $this->computeStats());

// With Carbon for readability
$stats = Cache::remember('stats', now()->addHour(), fn () => $this->computeStats());

// With conditional TTL
$ttl = $important ? 3600 : 300;
$data = Cache::remember('data', $ttl, fn () => $this->fetchData());
```

### DON'T ❌
```php
// Verbose manual pattern
$stats = Cache::get('stats');
if (! $stats) {
    $stats = $this->computeStats();
    Cache::put('stats', $stats, 3600);
}

// Race condition - two requests might both compute
if (! Cache::has('stats')) {
    Cache::put('stats', $this->computeStats(), 3600);
}
```

### For File Cache: Use Longer TTLs

File cache has I/O overhead. Cache longer to reduce disk reads.

```php
// ❌ Too short for file cache - frequent disk reads
Cache::remember('users', 10, fn () => User::all());

// ✅ Longer TTL reduces I/O
Cache::remember('users', 3600, fn () => User::all());
```

---

## 2. Array Cache for Per-Request Memoization

Array cache stores data in memory for the duration of a single request. Perfect for avoiding redundant computations.

### DO ✅
```php
// config/cache.php
'array' => [
    'driver' => 'array',
    'serialize' => false,
],

// Usage - fast, no disk I/O
$settings = Cache::store('array')->remember('settings', 3600, fn () => Settings::all());

// Multiple calls in same request = instant
$settings = Cache::store('array')->get('settings'); // From memory
$settings = Cache::store('array')->get('settings'); // Still from memory
```

### When to Use Array Cache

✅ **Use array cache when:**
- Data needed multiple times in single request
- Expensive computation (not DB query)
- Temporary data that doesn't need persistence
- Testing (fast, no side effects)

❌ **Don't use array cache when:**
- Data needed across multiple requests
- Data must survive process restarts
- Sharing data between workers/processes

---

## 3. Use `once()` for Per-Request Memoization

`once()` memoizes a function's return value for the lifetime of the object (or request for closures). Pure in-memory, no cache store overhead.

### DO ✅
```php
class User extends Model
{
    public function roles(): Collection
    {
        // First call: loads from DB
        // Subsequent calls: returns cached result
        return once(fn () => $this->loadRoles());
    }
    
    public function permissions(): Collection
    {
        return once(function () {
            return $this->roles->flatMap->permissions->unique();
        });
    }
}

// In controller - called multiple times, computed once
$user->roles; // DB query
$user->roles; // Cached
$user->roles; // Still cached
```

### `once()` vs `Cache::store('array')` vs `Cache::memo()`

| Method | Scope | Cache Store | Use Case |
|--------|-------|-------------|----------|
| `once()` | Object/Request | None (pure memory) | Model methods, closures |
| `Cache::store('array')` | Request | Array driver | Service layer, helpers |
| `Cache::memo()` | Request | Any driver | Cross-store memoization |

### DON'T ❌
```php
// ❌ Property-based caching (doesn't work across instances)
class UserService
{
    private ?Collection $cachedUsers = null;
    
    public function getUsers(): Collection
    {
        if ($this->cachedUsers === null) {
            $this->cachedUsers = User::all();
        }
        return $this->cachedUsers;
    }
}

// ✅ Use once() instead
class UserService
{
    public function getUsers(): Collection
    {
        return once(fn () => User::all());
    }
}
```

---

## 4. File Cache Management

File cache is persistent but requires proper management to avoid disk bloat.

### Configure File Cache Properly

```php
// config/cache.php
'file' => [
    'driver' => 'file',
    'path' => storage_path('framework/cache/data'),
    'permission' => 0775,
],
```

### Cache Key Naming Convention

Use descriptive, namespaced keys to avoid collisions.

```php
// ✅ Good: Namespaced and descriptive
Cache::remember('users:profile:123', 3600, fn () => User::find(123));
Cache::remember('posts:latest:10', 300, fn () => Post::latest()->take(10)->get());
Cache::remember('settings:app:general', 86400, fn () => Setting::getGroup('general'));

// ❌ Bad: Generic keys
Cache::remember('user', 3600, fn () => User::find(123)); // Which user?
Cache::remember('data', 300, fn () => Post::all()); // What data?
```

### Cache Invalidation Without Tags

Since file cache doesn't support tags, use key patterns and manual invalidation.

```php
// ✅ Pattern: Prefix-based invalidation
class UserRepository
{
    public function find(int $id): ?User
    {
        return Cache::remember("users:{$id}", 3600, fn () => User::find($id));
    }
    
    public function update(User $user, array $data): User
    {
        $user->update($data);
        
        // Invalidate specific key
        Cache::forget("users:{$user->id}");
        
        // Invalidate related keys
        Cache::forget("users:{$user->id}:profile");
        Cache::forget("users:{$user->id}:permissions");
        
        return $user;
    }
}

// ✅ Pattern: Version-based invalidation
class SettingsService
{
    public function get(string $key): mixed
    {
        $version = Cache::get('settings:version', 1);
        return Cache::remember("settings:v{$version}:{$key}", 86400, fn () => Setting::get($key));
    }
    
    public function update(string $key, mixed $value): void
    {
        Setting::set($key, $value);
        
        // Increment version to invalidate all settings
        Cache::increment('settings:version');
    }
}
```

### Automated Cache Cleanup

File cache accumulates over time. Set up automated cleanup.

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    // Clean expired cache files daily
    $schedule->call(function () {
        Artisan::call('cache:prune-stale');
    })->daily();
    
    // Or manually delete old files
    $schedule->call(function () {
        $path = storage_path('framework/cache/data');
        $files = File::files($path);
        
        foreach ($files as $file) {
            // Delete files older than 7 days
            if (File::lastModified($file) < now()->subDays(7)->timestamp) {
                File::delete($file);
            }
        }
    })->weekly();
}
```

---

## 5. Cache Warming for File Cache

File cache is slow on first read. Pre-warm cache for critical data.

### DO ✅
```php
// app/Console/Commands/WarmCache.php
class WarmCache extends Command
{
    protected $signature = 'cache:warm';
    protected $description = 'Pre-warm cache for critical data';

    public function handle(): void
    {
        $this->info('Warming cache...');
        
        // Warm user data
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                Cache::remember("users:{$user->id}", 3600, fn () => $user);
            }
        });
        
        // Warm settings
        Cache::remember('settings:app', 86400, fn () => Setting::all());
        
        // Warm latest posts
        Cache::remember('posts:latest', 300, fn () => Post::latest()->take(20)->get());
        
        $this->info('Cache warmed successfully!');
    }
}

// Run on deployment
// php artisan cache:warm

// Or schedule before peak hours
protected function schedule(Schedule $schedule): void
{
    $schedule->command('cache:warm')->dailyAt('06:00');
}
```

### DON'T ❌
```php
// ❌ Cache warming in controller (blocks first user)
public function index()
{
    if (! Cache::has('posts:latest')) {
        Cache::remember('posts:latest', 300, fn () => Post::latest()->take(20)->get());
    }
    
    return view('posts.index');
}
```

---

## 6. Cache Locks for Race Conditions

Prevent multiple processes from computing the same cache value simultaneously.

### DO ✅
```php
// For file cache: Use Cache::lock() with blocking
$lock = Cache::lock('computing-stats', 10);

if ($lock->get()) {
    try {
        $stats = Cache::remember('stats', 3600, fn () => $this->computeStats());
    } finally {
        $lock->release();
    }
} else {
    // Another process is computing, wait or use stale data
    $stats = Cache::get('stats') ?? $this->computeStats();
}

// Or use block() to wait
$stats = Cache::lock('computing-stats', 10)->block(5, function () {
    return Cache::remember('stats', 3600, fn () => $this->computeStats());
});
```

### For High-Traffic: Stale-While-Revalidate Pattern

Since `Cache::flexible()` requires queue support, implement manually for file cache.

```php
class StaleWhileRevalidateCache
{
    public function get(string $key, int $freshTtl, int $staleTtl, Closure $callback): mixed
    {
        $freshKey = "{$key}:fresh";
        $staleKey = "{$key}:stale";
        $lockKey = "{$key}:lock";
        
        // Try fresh cache
        if ($fresh = Cache::get($freshKey)) {
            return $fresh;
        }
        
        // Try stale cache
        if ($stale = Cache::get($staleKey)) {
            // Refresh in background (if not already refreshing)
            if (Cache::lock($lockKey, 10)->get()) {
                dispatch(fn () => $this->refresh($key, $freshTtl, $staleTtl, $callback));
            }
            return $stale;
        }
        
        // No cache, compute synchronously
        return $this->refresh($key, $freshTtl, $staleTtl, $callback);
    }
    
    private function refresh(string $key, int $freshTtl, int $staleTtl, Closure $callback): mixed
    {
        $value = $callback();
        
        Cache::put("{$key}:fresh", $value, $freshTtl);
        Cache::put("{$key}:stale", $value, $staleTtl);
        Cache::forget("{$key}:lock");
        
        return $value;
    }
}

// Usage
$service = app(StaleWhileRevalidateCache::class);
$users = $service->get('users', 300, 600, fn () => User::all());
```

---

## 7. Performance Considerations for File Cache

File cache has I/O overhead. Optimize to minimize disk reads.

### DO ✅
```php
// ✅ Cache larger chunks, not individual records
$users = Cache::remember('users:all', 3600, fn () => User::all());
$user = $users->find(123); // In-memory filtering

// ❌ Don't cache individual records (too many file reads)
$user = Cache::remember('users:123', 3600, fn () => User::find(123));
$user = Cache::remember('users:124', 3600, fn () => User::find(124));
$user = Cache::remember('users:125', 3600, fn () => User::find(125));

// ✅ Use longer TTLs for file cache
Cache::remember('settings', 86400, fn () => Setting::all()); // 24 hours

// ❌ Short TTLs cause frequent disk reads
Cache::remember('settings', 10, fn () => Setting::all()); // 10 seconds
```

### Cache Size Limits

File cache can grow unbounded. Monitor and limit cache size.

```php
// Monitor cache size
class CacheMonitor
{
    public function getSize(): int
    {
        $path = storage_path('framework/cache/data');
        $size = 0;
        
        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }
    
    public function isTooLarge(int $maxMb = 500): bool
    {
        return $this->getSize() > ($maxMb * 1024 * 1024);
    }
}

// Alert if cache too large
if (app(CacheMonitor::class)->isTooLarge()) {
    Log::warning('Cache size exceeded 500MB');
}
```

---

## 8. Cache Serialization

File cache serializes data. Choose the right serialization strategy.

### DO ✅
```php
// ✅ Cache simple data structures
Cache::remember('users:list', 3600, fn () => User::all()->toArray());
Cache::remember('settings', 3600, fn () => Setting::pluck('value', 'key'));

// ✅ Cache DTOs or simple objects
$data = Cache::remember('report', 3600, fn () => new ReportData(...));
```

### DON'T ❌
```php
// ❌ Cache Eloquent models (large, complex serialization)
Cache::remember('users', 3600, fn () => User::all()); // ❌ Large serialized objects

// ❌ Cache closures or resources
Cache::remember('callback', 3600, fn () => function () { /* ... */ }); // ❌ Can't serialize

// ❌ Cache database connections
Cache::remember('db', 3600, fn () => DB::connection()); // ❌ Can't serialize
```

---

## 9. Cache Invalidation Strategies

Without tags, use strategic invalidation patterns.

### Strategy 1: Event-Based Invalidation

```php
// Listen to model events
class UserObserver
{
    public function updated(User $user): void
    {
        Cache::forget("users:{$user->id}");
        Cache::forget('users:list');
        Cache::forget("users:{$user->id}:profile");
    }
    
    public function deleted(User $user): void
    {
        Cache::forget("users:{$user->id}");
        Cache::forget('users:list');
    }
}

// Register in AppServiceProvider
User::observe(UserObserver::class);
```

### Strategy 2: Time-Based Invalidation

```php
// Cache with short TTL for frequently changing data
Cache::remember('posts:latest', 60, fn () => Post::latest()->take(10)->get());

// Cache with long TTL for rarely changing data
Cache::remember('settings:app', 86400, fn () => Setting::all());
```

### Strategy 3: Manual Invalidation

```php
class PostService
{
    public function create(array $data): Post
    {
        $post = Post::create($data);
        
        // Invalidate related caches
        Cache::forget('posts:latest');
        Cache::forget("posts:author:{$post->author_id}");
        Cache::forget('posts:count');
        
        return $post;
    }
}
```

---

## 10. Multi-Layer Caching

Combine array cache (fast) with file cache (persistent).

### DO ✅
```php
class MultiLayerCache
{
    public function get(string $key, int $ttl, Closure $callback): mixed
    {
        // Layer 1: Array cache (per-request, fastest)
        if ($value = Cache::store('array')->get($key)) {
            return $value;
        }
        
        // Layer 2: File cache (persistent, slower)
        if ($value = Cache::store('file')->get($key)) {
            Cache::store('array')->put($key, $value, $ttl);
            return $value;
        }
        
        // Layer 3: Compute
        $value = $callback();
        
        Cache::store('file')->put($key, $value, $ttl);
        Cache::store('array')->put($key, $value, $ttl);
        
        return $value;
    }
    
    public function forget(string $key): void
    {
        Cache::store('array')->forget($key);
        Cache::store('file')->forget($key);
    }
}

// Usage
$cache = app(MultiLayerCache::class);
$users = $cache->get('users', 3600, fn () => User::all());
```

---

## 11. Cache Configuration for Production

Optimize file cache for production environments.

### config/cache.php

```php
return [
    'default' => env('CACHE_DRIVER', 'file'),
    
    'stores' => [
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
        
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'permission' => 0775,
        ],
        
        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
            'lock_connection' => null,
        ],
        
        // Use failover for reliability
        'failover' => [
            'driver' => 'failover',
            'stores' => ['file', 'database'],
        ],
    ],
    
    // Prefix all cache keys
    'prefix' => env('CACHE_PREFIX', 'laravel_cache_'),
];
```

### Environment-Specific Configuration

```php
// .env (production)
CACHE_DRIVER=file
CACHE_PREFIX=prod_cache_

// .env (local)
CACHE_DRIVER=array
CACHE_PREFIX=local_cache_
```

---

## 12. Testing Cache Behavior

Test cache logic without relying on actual cache store.

### DO ✅
```php
use Illuminate\Support\Facades\Cache;

test('it caches user data', function () {
    Cache::shouldReceive('remember')
        ->once()
        ->with('users:123', 3600, Mockery::type('Closure'))
        ->andReturn(User::factory()->make());
    
    $user = (new UserService)->find(123);
    
    expect($user)->toBeInstanceOf(User::class);
});

test('it invalidates cache on update', function () {
    $user = User::factory()->create();
    
    Cache::put("users:{$user->id}", $user, 3600);
    
    $user->update(['name' => 'New Name']);
    
    expect(Cache::has("users:{$user->id}"))->toBeFalse();
});

// Integration test
test('it returns cached data on second call', function () {
    $service = new UserService();
    
    $first = $service->find(123); // Computes
    $second = $service->find(123); // From cache
    
    expect($first)->toEqual($second);
});
```

---

## Summary: File/Array Cache Checklist

Before deploying with file/array cache:

- [ ] Using `Cache::remember()` instead of manual get/put
- [ ] Appropriate TTLs (longer for file cache)
- [ ] Namespaced cache keys to avoid collisions
- [ ] Cache invalidation strategy (events, time-based, manual)
- [ ] Cache warming for critical data
- [ ] Automated cleanup for file cache
- [ ] Cache locks for race conditions
- [ ] Multi-layer caching (array + file) for performance
- [ ] Monitoring cache size and performance
- [ ] Testing cache behavior
- [ ] Failover configuration for reliability

---

## Anti-Patterns to Avoid

### 1. Caching Everything
```php
// ❌ Don't cache small, fast queries
Cache::remember('user:1', 3600, fn () => User::find(1)); // Fast query, cache overhead > benefit

// ✅ Cache expensive operations
Cache::remember('report:annual', 86400, fn () => $this->generateAnnualReport());
```

### 2. Stale Data Forever
```php
// ❌ Very long TTL without invalidation
Cache::remember('settings', 9999999, fn () => Setting::all()); // Never updates

// ✅ Reasonable TTL + invalidation
Cache::remember('settings', 86400, fn () => Setting::all());
// + Observer to invalidate on update
```

### 3. Cache Stampede
```php
// ❌ All requests compute when cache expires
if (! $data = Cache::get('expensive')) {
    $data = $this->computeExpensive(); // 100 requests all compute!
    Cache::put('expensive', $data, 60);
}

// ✅ Use locks
$lock = Cache::lock('computing-expensive', 10);
if ($lock->get()) {
    try {
        $data = Cache::remember('expensive', 60, fn () => $this->computeExpensive());
    } finally {
        $lock->release();
    }
}
```

### 4. Caching Mutable Objects
```php
// ❌ Cached object gets modified
$user = Cache::remember('user:1', 3600, fn () => User::find(1));
$user->name = 'Modified'; // ❌ Modifies cached object!
$user->save(); // ❌ Doesn't update cache

// ✅ Clone or use immutable data
$user = clone Cache::remember('user:1', 3600, fn () => User::find(1));
$user->name = 'Modified';
$user->save();
Cache::forget('user:1'); // Invalidate cache
```

### 5. Nested Cache Calls
```php
// ❌ Cache within cache callback
Cache::remember('outer', 3600, function () {
    return Cache::remember('inner', 3600, fn () => expensive());
});

// ✅ Flatten cache logic
$inner = Cache::remember('inner', 3600, fn () => expensive());
$outer = Cache::remember('outer', 3600, fn () => process($inner));
```