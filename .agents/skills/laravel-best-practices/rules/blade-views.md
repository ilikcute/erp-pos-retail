# Blade & Views Best Practices

Best practices for Laravel Blade templates, focusing on maintainability, performance, and security.

## Core Principles

1. **Components over includes** — Use Blade components for reusable UI elements
2. **Explicit over implicit** — Pass data explicitly, avoid global variables
3. **Separation of concerns** — No business logic in views, no HTML in controllers
4. **Accessibility first** — Use semantic HTML, ARIA attributes, proper heading hierarchy
5. **Performance aware** — Minimize database queries, use caching for expensive operations

---

## 1. Use `$attributes->merge()` in Component Templates

Hardcoding classes prevents consumers from adding their own. `merge()` combines class attributes cleanly.

### DO ✅
```blade
{{-- resources/views/components/alert.blade.php --}}
@props(['type' => 'info', 'dismissible' => false])

<div {{ $attributes->merge(['class' => 'alert alert-' . $type]) }}>
    @if($dismissible)
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    @endif
    
    {{ $slot }}
</div>

{{-- Usage --}}
<x-alert type="success" class="my-4 shadow-lg">
    Operation completed successfully!
</x-alert>

{{-- Result: class="alert alert-success my-4 shadow-lg" --}}
```

### DON'T ❌
```blade
{{-- Hardcoded class prevents customization --}}
<div class="alert alert-{{ $type }}">
    {{ $slot }}
</div>

{{-- Usage --}}
<x-alert type="success" class="my-4">
    {{-- Result: class="my-4" (overwrites alert classes!) --}}
</x-alert>
```

### Advanced Attribute Merging

```blade
{{-- Merge multiple attributes --}}
<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'btn btn-primary',
    'disabled' => $loading ?? false,
]) }}>
    {{ $slot }}
</button>

{{-- Conditional merging --}}
<div {{ $attributes->class([
    'card',
    'card-bordered' => $bordered ?? false,
    'card-shadow' => $shadow ?? true,
])->merge(['class' => 'my-4']) }}>
    {{ $slot }}
</div>
```

---

## 2. Use `@pushOnce` for Per-Component Scripts

If a component renders inside a `@foreach`, `@push` inserts the script N times. `@pushOnce` guarantees it's included exactly once.

### DO ✅
```blade
{{-- resources/views/components/datepicker.blade.php --}}
<input type="text" {{ $attributes }} data-datepicker>

@pushOnce('scripts')
    <script>
        document.querySelectorAll('[data-datepicker]').forEach(el => {
            new Datepicker(el);
        });
    </script>
@endPushOnce

@pushOnce('styles')
    <link rel="stylesheet" href="{{ asset('css/datepicker.css') }}">
@endPushOnce

{{-- Layout file --}}
<head>
    @stack('styles')
</head>
<body>
    {{ $slot }}
    
    @stack('scripts')
</body>
```

### DON'T ❌
```blade
{{-- Script pushed N times in foreach loop --}}
@foreach ($items as $item)
    <x-datepicker />
    
    @push('scripts')
        <script>/* Datepicker init */</script> {{-- ❌ Duplicated N times --}}
    @endpush
@endforeach
```

---

## 3. Prefer Blade Components Over `@include`

`@include` shares all parent variables implicitly (hidden coupling). Components have explicit props, attribute bags, and slots.

### DO ✅
```blade
{{-- Component with explicit props --}}
<x-user-card :user="$user" :show-email="true" />

{{-- Component definition --}}
@props(['user', 'showEmail' => false])

<div class="user-card">
    <h3>{{ $user->name }}</h3>
    
    @if($showEmail)
        <p>{{ $user->email }}</p>
    @endif
    
    {{ $slot }}
</div>
```

### DON'T ❌
```blade
{{-- Include with implicit variable sharing --}}
@include('partials.user-card', ['user' => $user])

{{-- partials/user-card.blade.php --}}
{{-- ❌ Can access ANY variable from parent: $user, $posts, $settings, etc. --}}
<div class="user-card">
    <h3>{{ $user->name }}</h3>
    
    {{-- ❌ Hidden coupling: relies on $currentRole from parent --}}
    @if($currentRole === 'admin')
        <span>Admin</span>
    @endif
</div>
```

### When to Use `@include`

Only use `@include` for:
- Simple partials with no logic (headers, footers)
- When you explicitly need variable sharing
- Legacy code that hasn't been migrated to components

---

## 4. Use View Composers for Shared View Data

If every controller rendering a sidebar must pass `$categories`, that's duplicated code. A View Composer centralizes it.

### DO ✅
```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    // Share with specific views
    view()->composer('partials.sidebar', function ($view) {
        $view->with('categories', Category::withCount('posts')->get());
    });
    
    // Share with multiple views
    view()->composer(['partials.sidebar', 'partials.header'], function ($view) {
        $view->with('currentUser', auth()->user());
    });
    
    // Share with all views (use sparingly)
    view()->share('appName', config('app.name'));
}

// Or use dedicated View Composer class
class SidebarComposer
{
    public function compose(View $view): void
    {
        $view->with('categories', Cache::remember('sidebar.categories', 3600, function () {
            return Category::withCount('posts')->get();
        }));
    }
}

// Register in AppServiceProvider
view()->composer('partials.sidebar', SidebarComposer::class);
```

### DON'T ❌
```php
// ❌ Repeating data loading in every controller
class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('posts')->get();
        return view('home', compact('categories'));
    }
}

class PostController extends Controller
{
    public function show(Post $post)
    {
        $categories = Category::withCount('posts')->get();
        return view('posts.show', compact('post', 'categories'));
    }
}

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        $categories = Category::withCount('posts')->get();
        return view('categories.show', compact('category', 'categories'));
    }
}
```

---

## 5. Use Blade Fragments for Partial Re-Renders (htmx/Turbo)

A single view can return either the full page or just a fragment, keeping routing clean.

### DO ✅
```blade
{{-- resources/views/dashboard.blade.php --}}
<x-layouts.app>
    <h1>Dashboard</h1>
    
    @fragment('user-list')
        <div id="user-list">
            @foreach ($users as $user)
                <div class="user">{{ $user->name }}</div>
            @endforeach
        </div>
    @endfragment
</x-layouts.app>
```

```php
// Controller
public function index(Request $request)
{
    $users = User::latest()->paginate();
    
    // Return full page or just fragment based on request
    return view('dashboard', compact('users'))
        ->fragmentIf($request->hasHeader('HX-Request'), 'user-list');
}
```

### DON'T ❌
```php
// ❌ Separate routes for full page and partial
public function index()
{
    return view('dashboard', ['users' => User::all()]);
}

public function userList()
{
    return view('partials.user-list', ['users' => User::all()]);
}
```

---

## 6. Use `@aware` for Deeply Nested Component Props

Avoids re-passing parent props through every level of nested components.

### DO ✅
```blade
{{-- Parent component --}}
<x-card theme="dark" padding="lg">
    <x-card-header>
        <x-card-title>My Title</x-card-title>
    </x-card-header>
    
    <x-card-body>
        Content here
    </x-card-body>
</x-card>

{{-- card.blade.php --}}
@props(['theme' => 'light', 'padding' => 'md'])

<div class="card card-{{ $theme }} p-{{ $padding }}">
    {{ $slot }}
</div>

{{-- card-title.blade.php --}}
@aware(['theme' => 'light'])
@props(['title'])

<h2 class="text-{{ $theme === 'dark' ? 'white' : 'dark' }}">
    {{ $title }}
</h2>
```

### DON'T ❌
```blade
{{-- ❌ Passing props through every level --}}
<x-card theme="dark">
    <x-card-header theme="dark">
        <x-card-title theme="dark" title="My Title" />
    </x-card-header>
</x-card>
```

---

## 7. Template Inheritance Best Practices

Use layouts and sections to maintain consistent page structure.

### DO ✅
```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>@yield('title', config('app.name'))</title>
    
    {{-- Styles stack --}}
    @stack('styles')
</head>
<body class="@yield('body-class')">
    @include('partials.header')
    
    <main class="container mx-auto px-4 py-8">
        @yield('content')
    </main>
    
    @include('partials.footer')
    
    {{-- Scripts stack --}}
    @stack('scripts')
</body>
</html>

{{-- resources/views/posts/index.blade.php --}}
@extends('layouts.app')

@section('title', 'All Posts - ' . config('app.name'))

@section('body-class', 'bg-gray-50')

@section('content')
    <h1>All Posts</h1>
    
    @foreach ($posts as $post)
        <x-post-card :post="$post" />
    @endforeach
    
    {{ $posts->links() }}
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/posts.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/posts.js') }}"></script>
@endpush
```

### DON'T ❌
```blade
{{-- ❌ Copy-pasting full HTML in every view --}}
<!DOCTYPE html>
<html>
<head>
    <title>Posts</title>
    {{-- ❌ Duplicated meta tags, styles, scripts --}}
</head>
<body>
    <h1>Posts</h1>
</body>
</html>
```

---

## 8. Security: Prevent XSS and Injection

Always escape user input and use proper directives.

### DO ✅
```blade
{{-- ✅ Automatic escaping with {{ }} --}}
<p>Hello, {{ $user->name }}</p>

{{-- ✅ Use {!! !!} only for trusted, sanitized HTML --}}
<div>{!! $trustedHtml !!}</div>

{{-- ✅ Escape in attributes --}}
<input value="{{ old('email', $user->email) }}">

{{-- ✅ CSRF token in forms --}}
<form method="POST" action="/posts">
    @csrf
    <input type="text" name="title">
</form>

{{-- ✅ Method spoofing --}}
<form method="POST" action="/posts/{{ $post->id }}">
    @csrf
    @method('PUT')
    <input type="text" name="title">
</form>
```

### DON'T ❌
```blade
{{-- ❌ Unescaped user input - XSS vulnerability --}}
<p>Hello, {!! $user->name !!}</p>

{{-- ❌ Missing CSRF token --}}
<form method="POST" action="/posts">
    <input type="text" name="title">
</form>

{{-- ❌ User input in JavaScript without escaping --}}
<script>
    var userName = "{{ $user->name }}"; {{-- ❌ Can break if name contains quotes --}}
</script>

{{-- ✅ Correct way to pass data to JavaScript --}}
<script>
    var userName = @json($user->name); {{-- ✅ Properly escaped JSON --}}
</script>
```

---

## 9. Performance: Minimize Database Queries

Avoid N+1 queries and expensive operations in loops.

### DO ✅
```blade
{{-- ✅ Eager load relationships --}}
@php
    $posts->load(['author', 'comments']);
@endphp

@foreach ($posts as $post)
    <article>
        <h2>{{ $post->title }}</h2>
        <p>By {{ $post->author->name }}</p> {{-- ✅ No query --}}
        <p>{{ $post->comments_count }} comments</p> {{-- ✅ No query --}}
    </article>
@endforeach

{{-- ✅ Use withCount for counts --}}
@foreach ($posts as $post)
    <p>{{ $post->comments_count }} comments</p>
@endforeach

{{-- ✅ Cache expensive operations --}}
@php
    $categories = Cache::remember('sidebar.categories', 3600, function () {
        return Category::withCount('posts')->get();
    });
@endphp
```

### DON'T ❌
```blade
{{-- ❌ N+1 query in loop --}}
@foreach ($posts as $post)
    <p>By {{ $post->author->name }}</p> {{-- ❌ Query per post --}}
    <p>{{ $post->comments->count() }} comments</p> {{-- ❌ Query per post --}}
@endforeach

{{-- ❌ Query in loop --}}
@foreach ($users as $user)
    <p>{{ $user->posts()->count() }} posts</p> {{-- ❌ Query per user --}}
@endforeach
```

---

## 10. Accessibility (a11y) Best Practices

Build accessible templates from the start.

### DO ✅
```blade
{{-- ✅ Semantic HTML --}}
<main>
    <article>
        <header>
            <h1>{{ $post->title }}</h1>
            <time datetime="{{ $post->created_at->toW3cString() }}">
                {{ $post->created_at->format('M d, Y') }}
            </time>
        </header>
        
        <div>
            {!! $post->content !!}
        </div>
    </article>
</main>

{{-- ✅ Proper form labels --}}
<form>
    <label for="email">Email Address</label>
    <input type="email" id="email" name="email" required>
    
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>
    
    <button type="submit">Sign In</button>
</form>

{{-- ✅ ARIA attributes when needed --}}
<button 
    aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
    aria-controls="dropdown-menu"
>
    Menu
</button>

<div id="dropdown-menu" role="menu" hidden="{{ !$isOpen }}">
    <a href="/dashboard" role="menuitem">Dashboard</a>
    <a href="/profile" role="menuitem">Profile</a>
</div>

{{-- ✅ Skip navigation link --}}
<body>
    <a href="#main-content" class="sr-only focus:not-sr-only">
        Skip to main content
    </a>
    
    <nav><!-- Navigation --></nav>
    
    <main id="main-content">
        <!-- Main content -->
    </main>
</body>
```

### DON'T ❌
```blade
{{-- ❌ Non-semantic HTML --}}
<div class="article">
    <div class="title">{{ $post->title }}</div>
    <div class="date">{{ $post->created_at }}</div>
</div>

{{-- ❌ Missing form labels --}}
<form>
    <input type="email" name="email" placeholder="Email"> {{-- ❌ No label --}}
    <input type="password" name="password" placeholder="Password">
</form>

{{-- ❌ Generic button with no context --}}
<button>Click</button> {{-- ❌ What does this do? --}}

{{-- ❌ Missing alt text on images --}}
<img src="{{ $user->avatar }}"> {{-- ❌ No alt attribute --}}
```

---

## 11. Livewire Integration Best Practices

When using Livewire, follow these patterns for better performance and UX.

### DO ✅
```blade
{{-- ✅ Use wire:key for dynamic lists --}}
@foreach ($items as $item)
    <div wire:key="item-{{ $item->id }}">
        {{ $item->name }}
    </div>
@endforeach

{{-- ✅ Use wire:loading for visual feedback --}}
<button wire:click="save" wire:loading.attr="disabled">
    <span wire:loading.remove>Save</span>
    <span wire:loading>Saving...</span>
</button>

{{-- ✅ Use wire:dirty for unsaved changes indicator --}}
<input type="text" wire:model="title" wire:dirty.class="border-yellow-500">

{{-- ✅ Defer expensive updates --}}
<input type="text" wire:model.debounce.500ms="search">

{{-- ✅ Use wire:navigate for faster page transitions (Livewire 3) --}}
<a href="/posts/{{ $post->id }}" wire:navigate>
    {{ $post->title }}
</a>
```

### DON'T ❌
```blade
{{-- ❌ Missing wire:key causes DOM issues --}}
@foreach ($items as $item)
    <div>{{ $item->name }}</div> {{-- ❌ No key --}}
@endforeach

{{-- ❌ Synchronous updates on every keystroke --}}
<input type="text" wire:model="search"> {{-- ❌ Sends request on every keypress --}}

{{-- ❌ Loading entire page when only part changes --}}
<div wire:click="refreshPage">
    {{-- ❌ Re-renders entire component --}}
</div>
```

---

## 12. Alpine.js Integration Patterns

Lightweight JavaScript for interactive components.

### DO ✅
```blade
{{-- ✅ Dropdown with Alpine --}}
<div x-data="{ open: false }">
    <button @click="open = !open" @click.outside="open = false">
        Menu
    </button>
    
    <div x-show="open" x-transition>
        <a href="/dashboard">Dashboard</a>
        <a href="/profile">Profile</a>
    </div>
</div>

{{-- ✅ Modal with Alpine --}}
<div x-data="{ show: false }">
    <button @click="show = true">Open Modal</button>
    
    <div x-show="show" 
         x-transition
         @keydown.escape.window="show = false"
         class="modal-overlay"
    >
        <div class="modal-content" @click.outside="show = false">
            <h2>Modal Title</h2>
            <p>Modal content here</p>
            <button @click="show = false">Close</button>
        </div>
    </div>
</div>

{{-- ✅ Combine with Livewire --}}
<div x-data="{ search: '' }">
    <input 
        type="text" 
        x-model="search"
        @input.debounce.300ms="$wire.set('search', search)"
        placeholder="Search..."
    >
</div>
```

---

## 13. Asset Management

Organize and version your assets properly.

### DO ✅
```blade
{{-- ✅ Use asset() helper for public assets --}}
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<script src="{{ asset('js/app.js') }}"></script>

{{-- ✅ Use Vite for modern builds (Laravel 11+) --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- ✅ Conditional assets --}}
@if (app()->environment('local'))
    <script src="{{ asset('js/debug.js') }}"></script>
@endif

{{-- ✅ Preconnect for external resources --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">

{{-- ✅ Preload critical assets --}}
<link rel="preload" href="{{ asset('fonts/inter.woff2') }}" as="font" type="font/woff2" crossorigin>
```

### DON'T ❌
```blade
{{-- ❌ Hardcoded paths --}}
<link rel="stylesheet" href="/css/app.css">

{{-- ❌ Loading all assets on every page --}}
<script src="{{ asset('js/heavy-library.js') }}"></script> {{-- ❌ Only needed on specific pages --}}
```

---

## 14. Component Testing

Test your Blade components to ensure they render correctly.

### DO ✅
```php
// tests/Feature/Components/AlertTest.php
use App\View\Components\Alert;

test('alert component renders with correct classes', function () {
    $view = $this->component(Alert::class, ['type' => 'success']);
    
    $view->assertSee('alert');
    $view->assertSee('alert-success');
});

test('alert component is dismissible when prop is true', function () {
    $view = $this->component(Alert::class, [
        'type' => 'info',
        'dismissible' => true,
    ]);
    
    $view->assertSee('data-dismiss="alert"');
});

test('alert component renders slot content', function () {
    $view = $this->blade('<x-alert>Success message</x-alert>');
    
    $view->assertSee('Success message');
});
```

---

## 15. Common Blade Patterns

### Pagination
```blade
{{-- ✅ Simple pagination --}}
{{ $posts->links() }}

{{-- ✅ Custom pagination --}}
@if ($posts->hasPages())
    <nav role="navigation" aria-label="Pagination">
        <ul class="pagination">
            @if ($posts->onFirstPage())
                <li class="disabled" aria-disabled="true">
                    <span>Previous</span>
                </li>
            @else
                <li>
                    <a href="{{ $posts->previousPageUrl() }}" rel="prev">Previous</a>
                </li>
            @endif
            
            @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                <li class="{{ $page == $posts->currentPage() ? 'active' : '' }}">
                    <a href="{{ $url }}" aria-current="{{ $page == $posts->currentPage() ? 'page' : false }}">
                        {{ $page }}
                    </a>
                </li>
            @endforeach
            
            @if ($posts->hasMorePages())
                <li>
                    <a href="{{ $posts->nextPageUrl() }}" rel="next">Next</a>
                </li>
            @else
                <li class="disabled" aria-disabled="true">
                    <span>Next</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
```

### Forms with Validation
```blade
{{-- ✅ Form with error handling --}}
<form method="POST" action="/posts">
    @csrf
    
    <div class="form-group">
        <label for="title">Title</label>
        <input 
            type="text" 
            id="title" 
            name="title" 
            value="{{ old('title') }}"
            class="@error('title') is-invalid @enderror"
            required
        >
        
        @error('title')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="body">Body</label>
        <textarea 
            id="body" 
            name="body" 
            class="@error('body') is-invalid @enderror"
            required
        >{{ old('body') }}</textarea>
        
        @error('body')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
    
    <button type="submit">Create Post</button>
</form>
```

### Conditional Classes
```blade
{{-- ✅ Using $attributes->class() --}}
<div {{ $attributes->class([
    'card',
    'card-primary' => $primary ?? false,
    'card-lg' => $size === 'large',
    'p-4' => $padding ?? true,
]) }}>
    {{ $slot }}
</div>

{{-- Usage --}}
<x-card primary size="large">
    Content
</x-card>
```

---

## Summary: Blade Checklist

Before deploying views to production:

- [ ] All user input is escaped with `{{ }}`
- [ ] Forms have `@csrf` tokens
- [ ] No database queries in views (use eager loading)
- [ ] Components used instead of `@include` where appropriate
- [ ] Scripts use `@pushOnce` to avoid duplication
- [ ] Accessibility: semantic HTML, labels, alt text, ARIA attributes
- [ ] Performance: no N+1 queries, expensive operations cached
- [ ] Assets properly versioned with Vite or asset()
- [ ] Components tested with `blade()` test helper
- [ ] Template inheritance used consistently
- [ ] JavaScript data passed with `@json()` for safety

---

## Anti-Patterns to Avoid

### 1. Business Logic in Views
```blade
{{-- ❌ Complex logic in Blade --}}
@if ($user->orders()->where('status', 'completed')->where('created_at', '>', now()->subMonth())->count() > 5)
    <span>VIP Customer</span>
@endif

{{-- ✅ Move to model or view model --}}
@if ($user->isVip())
    <span>VIP Customer</span>
@endif
```

### 2. God Components
```blade
{{-- ❌ Component that does too much --}}
<x-mega-component 
    :users="$users"
    :posts="$posts"
    :settings="$settings"
    :permissions="$permissions"
/>

{{-- ✅ Break into smaller, focused components --}}
<x-user-list :users="$users" />
<x-post-list :posts="$posts" />
<x-settings-panel :settings="$settings" />
```

### 3. Inline Styles and Scripts
```blade
{{-- ❌ Inline CSS/JS --}}
<div style="color: red; font-size: 16px;">Error</div>
<script>
    function showAlert() {
        alert('Hello');
    }
</script>

{{-- ✅ Use classes and external files --}}
<div class="alert alert-danger">Error</div>
<script src="{{ asset('js/alerts.js') }}"></script>
```

### 4. Magic Numbers and Strings
```blade
{{-- ❌ Magic numbers --}}
@if ($user->role_id === 3)
    <span>Admin</span>
@endif

{{-- ✅ Use constants or enums --}}
@if ($user->role === UserRole::Admin)
    <span>Admin</span>
@endif
```

### 5. Deeply Nested Conditionals
```blade
{{-- ❌ Hard to read --}}
@if ($user)
    @if ($user->hasPosts())
        @if ($user->posts->count() > 10)
            <span>Prolific Writer</span>
        @endif
    @endif
@endif

{{-- ✅ Flatten with early returns or helper methods --}}
@if ($user?->isProlificWriter())
    <span>Prolific Writer</span>
@endif
```