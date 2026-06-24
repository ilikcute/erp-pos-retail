# Advanced Query Patterns

Best practices for complex Laravel/Eloquent queries, focused on performance and maintainability.

## 1. Use `addSelect()` Subqueries for Single Values from Has-Many

Instead of eager-loading an entire has-many relationship for a single value (like the latest timestamp), use a correlated subquery via `addSelect()`. This pulls the value directly in the main SQL query — zero extra queries.

### DO ✅
```php
// In Model
public function scopeWithLastLoginAt($query): void
{
    $query->addSelect([
        'last_login_at' => Login::select('created_at')
            ->whereColumn('user_id', 'users.id')
            ->latest()
            ->take(1),
    ]);
}

// In model casts (Laravel 11+)
protected function casts(): array
{
    return [
        'last_login_at' => 'datetime',
    ];
}

// Usage
$users = User::withLastLoginAt()->get();
echo $users->first()->last_login_at->diffForHumans();
```

### DON'T ❌
```php
// N+1 query - loads all logins for each user
$users = User::with('logins')->get();
$users->each(fn ($user) => $user->logins->last()->created_at);

// Or worse - query in loop
$users = User::all();
$users->each(fn ($user) => $user->logins()->latest()->first());
```

---

## 2. Use `latestOfMany()` for Latest Related Model

When you need the "latest" or "oldest" related model, use Laravel's built-in `latestOfMany()` instead of manual subqueries.

### DO ✅
```php
// In Model - define relationship
public function lastLogin(): HasOne
{
    return $this->hasOne(Login::class)->latestOfMany();
}

public function firstLogin(): HasOne
{
    return $this->hasOne(Login::class)->oldestOfMany();
}

// Usage - eager load like normal relationship
$users = User::with(['lastLogin', 'firstLogin'])->get();
echo $users->first()->lastLogin->created_at;
```

### DON'T ❌
```php
// Manual subquery - error-prone and harder to read
public function scopeWithLastLogin($query): void
{
    $query->addSelect([
        'last_login_id' => Login::select('id')
            ->whereColumn('user_id', 'users.id')
            ->latest()
            ->take(1),
    ]);
}
```

---

## 3. Use Conditional Aggregates Instead of Multiple Count Queries

Replace N separate `count()` queries with a single query using `CASE WHEN` inside `selectRaw()`. Use `toBase()` to skip model hydration when you only need scalar values.

### DO ✅
```php
// Single query with conditional aggregates
$statuses = Feature::toBase()
    ->selectRaw("SUM(CASE WHEN status = 'Requested' THEN 1 ELSE 0 END) as requested")
    ->selectRaw("SUM(CASE WHEN status = 'Planned' THEN 1 ELSE 0 END) as planned")
    ->selectRaw("SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed")
    ->first();

echo $statuses->requested; // 42
echo $statuses->planned;   // 18
```

### DON'T ❌
```php
// N queries - one per status
$requested = Feature::where('status', 'Requested')->count();
$planned = Feature::where('status', 'Planned')->count();
$completed = Feature::where('status', 'Completed')->count();
```

---

## 4. Use `setRelation()` to Prevent Circular N+1

When a parent model is eager-loaded with its children, and the view also needs `$child->parent`, use `setRelation()` to inject the already-loaded parent rather than letting Eloquent fire N additional queries.

### DO ✅
```php
// Load feature with comments
$feature = Feature::with('comments.user')->find(1);

// Inject parent back into children to prevent N+1
$feature->comments->each->setRelation('feature', $feature);

// In Blade - no additional queries
@foreach ($feature->comments as $comment)
    <p>{{ $comment->feature->title }}</p> {{-- ✅ Zero queries --}}
@endforeach
```

### DON'T ❌
```php
// Without setRelation - triggers N queries
$feature = Feature::with('comments')->find(1);

@foreach ($feature->comments as $comment)
    <p>{{ $comment->feature->title }}</p> {{-- ❌ Query per comment --}}
@endforeach
```

---

## 5. Prefer `whereIn` + Subquery Over `whereHas` (Sometimes)

`whereHas()` emits a correlated `EXISTS` subquery that re-executes per row. Using `whereIn()` with a subquery lets the database use an index lookup instead.

### DO ✅
```php
// Index-friendly subquery - database executes subquery ONCE
$users = User::whereIn('company_id', 
    Company::where('name', 'like', '%Acme%')->select('id')
)->get();
```

### DON'T ❌
```php
// Correlated EXISTS - re-executes per row
$users = User::whereHas('company', fn ($q) => $q->where('name', 'like', '%Acme%'))->get();
```

### When to Use Each

| Use `whereIn` + subquery when: | Use `whereHas` when: |
|--------------------------------|----------------------|
| Subquery is highly selective (returns few rows) | Subquery returns many rows |
| Foreign key column is indexed | No index on foreign key |
| You need to reuse the subquery result | Complex conditions on relationship |

---

## 6. Sometimes Two Simple Queries Beat One Complex Query

Running a small, targeted secondary query and passing its results via `whereIn` is often faster than a single complex correlated subquery or join.

### DO ✅
```php
// Two simple queries - database optimizer works better
$recentCommentPostIds = Comment::where('created_at', '>', now()->subDays(7))
    ->pluck('post_id')
    ->unique();

$posts = Post::whereIn('id', $recentCommentPostIds)
    ->with('author')
    ->get();
```

### DON'T ❌
```php
// Complex join - harder to optimize
$posts = Post::join('comments', 'posts.id', '=', 'comments.post_id')
    ->where('comments.created_at', '>', now()->subDays(7))
    ->select('posts.*')
    ->distinct()
    ->get();
```

### When to Use Two Queries

- Secondary query is highly selective (uses index, returns < 1000 rows)
- You need to cache the intermediate result
- Database optimizer struggles with complex joins
- You want to avoid locking issues with joins

---

## 7. Use Compound Indexes Matching `orderBy` Column Order

When ordering by multiple columns, create a single compound index in the same column order as the `ORDER BY` clause. Individual single-column indexes cannot combine for multi-column sorts.

### DO ✅
```php
// Migration - compound index
Schema::table('users', function (Blueprint $table) {
    $table->index(['last_name', 'first_name']);
});

// Query - column order MUST match index
$users = User::query()
    ->orderBy('last_name')
    ->orderBy('first_name')
    ->paginate();
```

### DON'T ❌
```php
// Wrong column order - won't use index efficiently
$users = User::query()
    ->orderBy('first_name')  // ❌ Reversed order
    ->orderBy('last_name')
    ->paginate();
```

### Compound Index Rules

- Index `(A, B)` supports: `WHERE A = ? ORDER BY B`, `ORDER BY A, B`
- Index `(A, B)` does NOT support: `WHERE B = ? ORDER BY A`, `ORDER BY B, A`
- Leftmost prefix rule: Index `(A, B, C)` can be used for queries on `A`, `A+B`, or `A+B+C`

---

## 8. Use Correlated Subqueries for Has-Many Ordering

When sorting by a value from a has-many relationship, avoid joins (they duplicate rows). Use a correlated subquery inside `orderBy()` instead.

### DO ✅
```php
public function scopeOrderByLastLogin($query): void
{
    $query->orderByDesc(
        Login::select('created_at')
            ->whereColumn('user_id', 'users.id')
            ->latest()
            ->take(1)
    );
}

// Usage
$users = User::orderByLastLogin()->paginate();
```

### ⚠️ Performance Warning

- Subquery in `ORDER BY` executes for **every row**
- Slow on large datasets (> 100k rows)
- Ensure composite index exists: `(user_id, created_at)`

### Alternatives for Large Datasets

```php
// 1. Denormalize - store last_login_at in users table
Schema::table('users', function (Blueprint $table) {
    $table->timestamp('last_login_at')->nullable()->index();
});

// Update via observer or event
Login::created(function ($login) {
    $login->user()->update(['last_login_at' => $login->created_at]);
});

// 2. Materialized view / summary table (for analytics)
// 3. Cache sorted result if data doesn't need to be real-time
```

---

## 9. Use `withExists()` for Boolean Checks

Check if a relationship exists without loading the entire relation.

### DO ✅
```php
// Returns boolean attribute: $post->comments_exists
$posts = Post::withExists('comments')->get();

// Conditional exists with custom name
$posts = Post::withExists(['comments as has_recent_comments' => function ($q) {
    $q->where('created_at', '>', now()->subDays(7));
}])->get();

// Usage
@foreach ($posts as $post)
    @if ($post->comments_exists)
        <span>Has comments</span>
    @endif
@endforeach
```

### DON'T ❌
```php
// Load entire relation just to check existence
$posts = Post::with('comments')->get()->filter(fn ($p) => $p->comments->isNotEmpty());
```

---

## 10. Use Aggregate Methods: `withCount()`, `withSum()`, `withAvg()`, `withMin()`, `withMax()`

Laravel provides built-in methods for common aggregates on relationships.

### DO ✅
```php
// Multiple aggregates in single query
$posts = Post::withCount('comments')
    ->withSum('likes', 'value')
    ->withAvg('ratings', 'score')
    ->withMax('orders', 'total')
    ->withMin('prices', 'amount')
    ->get();

// Access
$post->comments_count;      // 42
$post->likes_sum_value;     // 1500
$post->ratings_avg_score;   // 4.5
$post->orders_max_total;    // 999.99
$post->prices_min_amount;   // 9.99

// Conditional aggregates
$posts = Post::withCount(['comments as recent_comments_count' => function ($q) {
    $q->where('created_at', '>', now()->subDays(7));
}])->get();
```

### DON'T ❌
```php
// N+1 queries
$posts = Post::all();
$posts->each(function ($post) {
    $post->comments_count = $post->comments()->count();
    $post->likes_sum = $post->likes()->sum('value');
});
```

---

## 11. Use `whereRelation()` as Shorthand for `whereHas()`

Cleaner syntax for simple relationship conditions.

### DO ✅
```php
// Clean whereRelation (Laravel 9+)
$posts = Post::whereRelation('author', 'active', true)->get();

// With operator
$posts = Post::whereRelation('author', 'created_at', '>', now()->subYear())->get();

// Multiple conditions
$posts = Post::whereRelation('author', 'active', true)
    ->whereRelation('category', 'published', true)
    ->get();
```

### DON'T ❌
```php
// Verbose whereHas for simple conditions
$posts = Post::whereHas('author', fn ($q) => $q->where('active', true))->get();
```

### When to Use `whereHas()`

Use `whereHas()` when you need:
- Complex conditions (OR, nested where)
- Closures with multiple clauses
- `orWhereHas()` for OR logic

---

## 12. Bulk Operations: `insert()`, `upsert()`, `updateOrInsert()`

Avoid loops for bulk data operations.

### DO ✅
```php
// Bulk insert - 1 query
Post::insert([
    ['title' => 'Post 1', 'body' => 'Body 1'],
    ['title' => 'Post 2', 'body' => 'Body 2'],
    ['title' => 'Post 3', 'body' => 'Body 3'],
]);

// Upsert (insert or update on conflict) - 1 query
Post::upsert(
    $data, // Array of arrays
    uniqueBy: ['slug'], // Conflict target (must have unique index)
    update: ['title', 'body', 'updated_at'] // Columns to update on conflict
);

// Update or insert single record
Post::updateOrInsert(
    ['slug' => 'hello-world'], // Search criteria
    ['title' => 'Hello', 'body' => 'World'] // Data to insert/update
);
```

### DON'T ❌
```php
// Loop insert - N queries
foreach ($data as $row) {
    Post::create($row);
}
```

### ⚠️ Warnings

- `insert()` does NOT trigger model events (creating, created, etc.)
- `insert()` does NOT set timestamps automatically - add them manually
- `upsert()` requires unique index on `uniqueBy` columns
- For large datasets (> 10k rows), use `chunk()` to avoid memory issues

---

## 13. `cursor()` vs `lazy()` vs `chunk()` - When to Use Each

Choose the right iteration method based on your needs.

### `cursor()` - Memory Efficient, Read-Only
```php
// Use when: Processing millions of rows, no modifications needed
User::cursor()->each(function ($user) {
    process($user); // Read-only operation
});

// ⚠️ Limitations:
// - Cannot use with relationships (no eager loading)
// - No lazy loading (will trigger N+1)
// - Cannot modify models (no save(), update())
```

### `lazy()` - Chunked Internally, Supports Relationships
```php
// Use when: Need to modify models, have relationships
User::lazy(100)->each(function ($user) {
    $user->update(['processed' => true]); // ✅ Can modify
});

// Supports eager loading
User::with('posts')->lazy()->each(function ($user) {
    echo $user->posts->count(); // ✅ No N+1
});
```

### `chunk()` - Manual Chunking, Good for Batch Processing
```php
// Use when: Processing in batches (e.g., send emails per 100 users)
User::chunk(100, function ($users) {
    Mail::to($users)->send(new Newsletter());
});

// ⚠️ Use chunkById() when updating (chunk() can skip/duplicate rows)
User::chunkById(100, function ($users) {
    $users->each->update(['status' => 'processed']);
});
```

### Decision Tree

```
Need to process large dataset?
├─ Read-only, no relationships? → cursor()
├─ Need to modify models? → lazy()
└─ Need batch processing (e.g., send emails)? → chunk()
    └─ Updating records? → chunkById()
```

---

## 14. Query Debugging: `toSql()`, `DB::listen()`, `explain()`

Tools for understanding and optimizing queries.

### Get Raw SQL
```php
// Get SQL with placeholders (?)
$sql = User::where('active', true)->toSql();
// "select * from `users` where `active` = ?"

// Get bindings
$bindings = User::where('active', true)->getBindings();
// [true]

// Combine for full query (for manual testing)
$sql = User::where('active', true)->toSql();
$bindings = User::where('active', true)->getBindings();
$fullSql = vsprintf(str_replace('?', "'%s'", $sql), $bindings);
// "select * from `users` where `active` = '1'"
```

### Log All Queries
```php
// In AppServiceProvider boot() method
DB::listen(function ($query) {
    Log::info('Query executed', [
        'sql' => $query->sql,
        'bindings' => $query->bindings,
        'time' => $query->time . 'ms',
    ]);
});

// Or use Telescope for visual debugging
```

### EXPLAIN Query
```php
// MySQL/PostgreSQL - understand query execution plan
$builder = User::where('active', true)->where('created_at', '>', now()->subMonth());

$explain = DB::select(
    'EXPLAIN ' . $builder->toSql(),
    $builder->getBindings()
);

// Check for:
// - type: Should be "ref" or "range", not "ALL" (full table scan)
// - key: Should show index being used
// - rows: Estimated rows to examine (lower is better)
```

---

## Summary: Performance Checklist

Before deploying queries to production:

- [ ] No N+1 queries (use `with()`, `withCount()`, `withExists()`)
- [ ] Select only needed columns (avoid `SELECT *` on large tables)
- [ ] Use appropriate indexes (check with `EXPLAIN`)
- [ ] Use `cursor()` or `lazy()` for large datasets
- [ ] Use bulk operations (`insert()`, `upsert()`) instead of loops
- [ ] Cache expensive queries when appropriate
- [ ] Test with production-like data volume