---
name: laravel-best-practices
description: "Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code (controllers, models, migrations, form requests, policies, jobs, commands, services, actions, and Eloquent queries). Use for code reviews, refactoring, and architectural decisions. Triggers: N+1 queries, caching, authorization, validation, error handling, queues, routing, API design, and any Laravel backend PHP code."
license: MIT
metadata:
  author: laravel
  target_version: "Laravel 11/12, PHP 8.2+"
---

# Laravel Best Practices

Best practices for Laravel, prioritized by impact. Every rule includes **what**, **why**, and **how** with concrete code examples.

## Core Principles (Read First)

1. **Consistency First** — Check sibling files before applying any rule. Follow existing patterns in the codebase. Inconsistency is worse than a suboptimal pattern.
2. **Explicit over Implicit** — Prefer clear, readable code over clever shortcuts.
3. **Fail Fast, Fail Loud** — Validate early, throw meaningful exceptions, never swallow errors silently.
4. **Separation of Concerns** — Controllers handle HTTP, Models handle data, Actions/Services handle business logic.
5. **Modern PHP** — Use PHP 8.2+ features: enums, readonly classes, constructor promotion, named arguments, match expressions, fibers.

## How AI Should Apply These Rules

When generating or reviewing Laravel code:

1. **Identify the file type** (controller, model, migration, etc.) and load relevant sections below.
2. **Check existing codebase patterns first** — if the project uses Repositories, keep using them (even if Actions are preferred by default).
3. **Always provide DO / DON'T examples** when suggesting changes.
4. **Prefer Laravel's built-in tools** (`Str`, `Arr`, `Http`, `Cache`, `Bus`) over raw PHP or third-party packages.
5. **Write code that works on Laravel 11/12 and PHP 8.2+** unless the user specifies otherwise.
6. **Include type hints and return types** on every method and property.

---

## 1. Database Performance

**Goal:** Minimize queries, avoid N+1, use indexes.

### DO ✅
```php
// Eager load to prevent N+1
$posts = Post::with(['author', 'comments.user'])->get();

// Select only needed columns
$users = User::select('id', 'name', 'email')->get();

// Use chunkById for large datasets (never chunk() on updating queries)
User::query()->chunkById(100, function ($users) {
    // process
});

// Use withCount instead of loading relations
$posts = Post::withCount('comments')->get();
// Then use: $post->comments_count

// Enable in AppServiceProvider (development only)
Model::preventLazyLoading(!app()->isProduction());
```

### DON'T ❌
```php
// N+1 query — loads author for EACH post
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // Query per iteration!
}

// SELECT * on large tables
$users = User::all();

// Querying in Blade templates
@foreach (User::all() as $user) ... @endforeach
```

---

## 2. Advanced Query Patterns

### DO ✅
```php
// Subquery instead of eager-loading entire relation for one value
$posts = Post::addSelect([
    'last_comment_at' => Comment::select('created_at')
        ->whereColumn('post_id', 'posts.id')
        ->latest()
        ->take(1)
])->get();

// Conditional aggregates instead of multiple count queries
$stats = User::selectRaw("
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
")->first();

// whereIn + pluck over whereHas for better index usage
$authorIds = Post::where('published', true)->pluck('author_id')->unique();
$authors = Author::whereIn('id', $authorIds)->get();
```

---

## 3. Security

### DO ✅
```php
// Always define $fillable or $guarded
class User extends Model {
    protected $fillable = ['name', 'email']; // explicit allowlist
}

// Use policies for authorization
class PostController extends Controller {
    public function update(Request $request, Post $post) {
        $this->authorize('update', $post);
        // ...
    }
}

// Validate file uploads strictly
$request->validate([
    'avatar' => ['required', 'image', 'mimes:jpg,png', 'max:2048', 'mimetypes:image/jpeg,image/png'],
]);

// Use encrypted cast for sensitive fields
class User extends Model {
    protected function casts(): array {
        return [
            'api_token' => 'encrypted',
        ];
    }
}

// env() ONLY inside config files
// config/services.php
'stripe_key' => env('STRIPE_KEY'),

// Use config() everywhere else
$apiKey = config('services.stripe.key');
```

### DON'T ❌
```php
// Raw SQL with user input — SQL injection risk
User::whereRaw("name = '$name'")->get();

// Using $request->all() — mass assignment vulnerability
User::create($request->all());

// env() outside config files
$apiKey = env('STRIPE_KEY'); // Returns null when config is cached!
```

---

## 4. Caching

### DO ✅
```php
// Use Cache::remember (not manual get/put)
$posts = Cache::remember('posts.all', 3600, fn () => Post::all());

// Flexible cache for stale-while-revalidate (Laravel 11+)
$posts = Cache::flexible('posts.all', [5, 10], fn () => Post::all());

// Cache tags for group invalidation
Cache::tags(['posts', 'users'])->flush();

// Locks for race conditions
$lock = Cache::lock('processing-order-123', 10);
if ($lock->get()) {
    try { /* process */ } finally { $lock->release(); }
}

// once() for per-request memoization
$user = once(fn () => User::find(1));
```

---

## 5. Eloquent Patterns

### DO ✅
```php
// Use PHP 8.1+ Enums for status columns
enum PostStatus: string {
    case Draft = 'draft';
    case Published = 'published';
}

class Post extends Model {
    protected function casts(): array {
        return ['status' => PostStatus::class];
    }
}

// Local scopes for reusable constraints
class Post extends Model {
    public function scopePublished(Builder $query): Builder {
        return $query->where('status', PostStatus::Published);
    }
}
// Usage: Post::published()->get();

// Use whereBelongsTo for cleaner queries
Post::whereBelongsTo($author)->get();

// Attribute accessors with new syntax
class User extends Model {
    protected function fullName(): Attribute {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }
}
```

### DON'T ❌
```php
// Hardcoding table names
DB::table('users')->where(...); // Use User::query() instead

// Old-style accessors/mutators
public function getFullNameAttribute() { ... }
```

---

## 6. Validation & Forms

### DO ✅
```php
// Use Form Request classes (not inline validation)
class StorePostRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('create', Post::class);
    }

    public function rules(): array {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['required', 'string'],
            'status' => ['required', Rule::enum(PostStatus::class)],
        ];
    }
}

// Controller uses validated() only
public function store(StorePostRequest $request) {
    Post::create($request->validated());
}

// Conditional validation with Rule::when
'email' => [
    Rule::when($request->boolean('subscribe'), ['required', 'email']),
],

// Use after() hook for complex cross-field validation
public function withValidator(Validator $validator): void {
    $validator->after(function ($validator) {
        if ($this->end_date < $this->start_date) {
            $validator->errors()->add('end_date', 'Must be after start date.');
        }
    });
}
```

### DON'T ❌
```php
// Inline validation in controllers
$request->validate([...]); // Only acceptable for tiny forms

// Using $request->all()
Post::create($request->all());
```

---

## 7. Architecture: Actions & Services

**Goal:** Keep controllers thin. Business logic belongs in Action classes.

### DO ✅
```php
// Single-purpose Action class
class CreatePostAction {
    public function __construct(
        private readonly ImageUploader $uploader,
    ) {}

    public function execute(User $author, array $data): Post {
        return DB::transaction(function () use ($author, $data) {
            $path = isset($data['image'])
                ? $this->uploader->upload($data['image'])
                : null;

            return $author->posts()->create([
                'title' => $data['title'],
                'body'  => $data['body'],
                'image' => $path,
                'status' => PostStatus::Draft,
            ]);
        });
    }
}

// Controller stays thin
class PostController extends Controller {
    public function store(StorePostRequest $request, CreatePostAction $action): JsonResponse {
        $post = $action->execute($request->user(), $request->validated());
        return response()->json($post, 201);
    }
}
```

### DON'T ❌
```php
// Fat controller with 80 lines of business logic
public function store(Request $request) {
    // upload image, validate, create post, send email, notify users...
}

// Generic "Service" classes that become god objects
class PostService { /* 20 methods doing unrelated things */ }
```

---

## 8. DTOs (Data Transfer Objects)

**Goal:** Type-safe data transfer between layers.

### DO ✅
```php
// Use readonly classes (PHP 8.2+)
readonly class PostData {
    public function __construct(
        public string $title,
        public string $body,
        public PostStatus $status,
        public ?string $imagePath = null,
    ) {}

    public static function fromRequest(StorePostRequest $request): self {
        return new self(
            title: $request->string('title')->toString(),
            body: $request->string('body')->toString(),
            status: PostStatus::from($request->string('status')),
        );
    }
}
```

---

## 9. API Resources

**Goal:** Transform models into consistent JSON responses.

### DO ✅
```php
class PostResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'author'     => UserResource::make($this->whenLoaded('author')),
            'comments_count' => $this->whenCounted('comments'),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}

// In controller
return PostResource::collection($posts);
```

---

## 10. Routing & Controllers

### DO ✅
```php
// Use route resources
Route::apiResource('posts', PostController::class);

// Implicit route model binding
public function show(Post $post) { ... }

// Scoped bindings for nested resources
Route::resource('posts.comments', CommentController::class)->scoped();

// Type-hint Form Requests for auto-validation
public function store(StorePostRequest $request) { ... }
```

---

## 11. Queue & Jobs

### DO ✅
```php
class ProcessPayment implements ShouldQueue {
    public $tries = 3;
    public $backoff = [10, 60, 300]; // exponential
    public $timeout = 120;
    public $retryAfter = 180; // must be > timeout

    public function handle(): void { /* ... */ }

    public function failed(Throwable $e): void {
        // notify admin, mark order as failed, etc.
    }
}

// Use ShouldBeUnique to prevent duplicates
class SendNewsletter implements ShouldQueue, ShouldBeUnique {
    public function uniqueId(): string {
        return $this->newsletter->id;
    }
}
```

---

## 12. Error Handling

### DO ✅
```php
// Custom exception class
class PaymentFailedException extends Exception {
    public function render(Request $request) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Payment failed'], 402);
        }
        return back()->withErrors(['payment' => $this->getMessage()]);
    }
}

// Use report() helper with context
try {
    // ...
} catch (PaymentFailedException $e) {
    report($e); // logs automatically
    throw $e;
}
```

---

## 13. Testing

### DO ✅
```php
use LazilyRefreshDatabase; // faster than RefreshDatabase

class PostTest extends TestCase {
    use LazilyRefreshDatabase;

    public function test_user_can_create_post(): void {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $this->actingAs($user)
            ->postJson('/posts', Post::factory()->raw())
            ->assertCreated();

        $this->assertModelExists($post);
    }
}
```

---

## 14. Migrations

### DO ✅
```php
return new class extends Migration {
    public function up(): void {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('status')->default('draft');
            $table->timestamps();

            // Add indexes in migration, not later
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('posts');
    }
};
```

### DON'T ❌
```php
// Never modify a migration that ran in production
// Create a NEW migration instead
```

---

## 15. Blade & Views

### DO ✅
```php
// Use components over @include
<x-alert type="success" :message="$message" />

// Merge attributes in components
<div {{ $attributes->merge(['class' => 'alert']) }}>
    {{ $slot }}
</div>

// Push scripts once per component
@pushOnce('scripts')
    <script>/* ... */</script>
@endPushOnce
```

### DON'T ❌
```php
// Business logic in Blade
@if (User::where('email', $email)->exists()) ... @endif
```

---

## 16. Conventions & Style

- **Naming:** `PostController`, `PostPolicy`, `StorePostRequest`, `CreatePostAction`, `post_status` (snake_case DB, camelCase PHP).
- **Helpers:** Prefer `Str::slug()` over `str_slug()`, `Arr::get()` over `array_get()`.
- **Readability:** Comments explain **WHY**, not **WHAT**. Good code is self-documenting.
- **No mixing concerns:** No HTML in PHP classes, no DB queries in Blade.

---

## When to Break the Rules

Rules have trade-offs. Break them when:

| Situation | Break | Reason |
|-----------|-------|--------|
| Tiny script / one-off command | Actions, DTOs | Over-engineering |
| Legacy codebase using Repositories | "Use Actions" | Consistency First |
| Performance-critical path | Readability | Raw SQL may be needed |
| Prototyping | Full validation/testing | Speed over perfection |

Always document WHY you broke the rule.