# Configuration Best Practices

Best practices for Laravel configuration management, focusing on security, maintainability, and performance.

## Core Principles

1. **Separation of concerns** — Config files define structure, `.env` provides values
2. **Environment-agnostic code** — Application code should not know about environments
3. **Type safety** — Validate and cast config values
4. **Performance** — Cache configs in production
5. **Security** — Never expose secrets in code or version control

---

## 1. `env()` Only in Config Files

Direct `env()` calls return `null` when config is cached with `php artisan config:cache`.

### DO ✅
```php
// config/services.php
return [
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],
    
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],
];

// Application code - anywhere in your app
$stripeKey = config('services.stripe.key');
$mailgunDomain = config('services.mailgun.domain');
```

### DON'T ❌
```php
// ❌ Returns null when config is cached!
class PaymentService
{
    public function charge()
    {
        $key = env('STRIPE_KEY'); // ❌ null in production!
        // ...
    }
}

// ❌ Even worse - scattered across codebase
class MailService
{
    public function send()
    {
        $domain = env('MAILGUN_DOMAIN'); // ❌ null!
        $secret = env('MAILGUN_SECRET'); // ❌ null!
    }
}
```

### Why This Happens

```bash
# When you run this in production:
php artisan config:cache

# Laravel merges all config files into a single cached file
# env() calls are NOT executed - they're replaced with actual values
# Direct env() calls in application code return null
```

---

## 2. Config File Organization

Structure your config files logically by domain, not by technical concern.

### DO ✅
```php
// config/services.php - Third-party services
return [
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'currency' => env('STRIPE_CURRENCY', 'usd'),
    ],
    
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],
    
    'aws' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => env('AWS_BUCKET'),
    ],
];

// config/app.php - Application-specific settings
return [
    'name' => env('APP_NAME', 'Laravel'),
    'version' => '1.0.0',
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    'pagination' => [
        'per_page' => env('APP_PAGINATION_PER_PAGE', 15),
    ],
];

// config/features.php - Feature flags
return [
    'new_dashboard' => env('FEATURE_NEW_DASHBOARD', false),
    'beta_api' => env('FEATURE_BETA_API', false),
    'maintenance_mode' => env('FEATURE_MAINTENANCE_MODE', false),
];
```

### DON'T ❌
```php
// ❌ Mixing unrelated configs
// config/settings.php
return [
    'stripe_key' => env('STRIPE_KEY'),
    'app_name' => env('APP_NAME'),
    'mailgun_secret' => env('MAILGUN_SECRET'),
    'pagination_per_page' => 15,
    'timezone' => 'UTC',
];

// ❌ Creating too many config files
// config/stripe.php
// config/stripe_webhook.php
// config/stripe_billing.php
// config/mailgun.php
// config/mailgun_domain.php
```

### Config File Structure Guidelines

```
✅ Good structure:
config/
├── app.php          (application settings)
├── auth.php         (authentication)
├── cache.php        (cache drivers)
├── database.php     (database connections)
├── features.php     (feature flags)
├── filesystems.php  (file storage)
├── logging.php      (log channels)
├── mail.php         (mail drivers)
├── queue.php        (queue connections)
├── services.php     (third-party services)
└── custom-domain.php (your domain-specific configs)

❌ Bad structure:
config/
├── settings.php     (everything mixed)
├── env.php          (just env vars)
├── constants.php    (not config, should be in code)
└── misc.php         (dumping ground)
```

---

## 3. Default Values and Fallbacks

Always provide sensible defaults for non-critical config values.

### DO ✅
```php
// config/app.php
return [
    // Required - no default (must be in .env)
    'key' => env('APP_KEY'),
    
    // Optional with sensible defaults
    'name' => env('APP_NAME', 'Laravel'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    
    // Nested defaults
    'pagination' => [
        'per_page' => env('APP_PAGINATION_PER_PAGE', 15),
        'max_per_page' => env('APP_PAGINATION_MAX', 100),
    ],
    
    // Boolean with default
    'debug' => env('APP_DEBUG', false),
];

// Usage with null coalescing for extra safety
$perPage = config('app.pagination.per_page', 15);
```

### DON'T ❌
```php
// ❌ No defaults for optional values
return [
    'timezone' => env('APP_TIMEZONE'), // null if not set!
    'locale' => env('APP_LOCALE'), // null if not set!
];

// ❌ Hardcoded defaults in application code
class PostController
{
    public function index()
    {
        $perPage = config('app.pagination.per_page') ?? 15; // ❌ Should be in config
        // ...
    }
}
```

### Required vs Optional Config

```php
// config/services.php
return [
    // Required - app won't work without these
    'stripe' => [
        'key' => env('STRIPE_KEY'), // null = error
        'secret' => env('STRIPE_SECRET'), // null = error
    ],
    
    // Optional - app works fine without these
    'analytics' => [
        'tracking_id' => env('ANALYTICS_ID'), // null = disabled
        'enabled' => env('ANALYTICS_ENABLED', false),
    ],
];

// Validate required configs in service provider
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (empty(config('services.stripe.key'))) {
            throw new \RuntimeException('Stripe key is required');
        }
    }
}
```

---

## 4. Environment-Specific Configuration

Use different config values for different environments.

### DO ✅
```php
// config/mail.php
return [
    'default' => env('MAIL_MAILER', 'smtp'),
    
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
        ],
        
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],
        
        'array' => [
            'transport' => 'array',
        ],
    ],
];

// .env (production)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org

// .env (local)
MAIL_MAILER=log
```

### Environment Detection

```php
// ✅ Use App::environment() or app()->isProduction()
class PaymentService
{
    public function charge()
    {
        if (app()->isProduction()) {
            // Use live Stripe
            $this->gateway = new LiveStripeGateway();
        } else {
            // Use mock for testing
            $this->gateway = new MockStripeGateway();
        }
    }
}

// ✅ Multiple environment checks
if (App::environment(['staging', 'production'])) {
    // Enable caching
}

// ✅ Environment-specific config
if (app()->isProduction()) {
    config(['cache.default' => 'redis']);
} else {
    config(['cache.default' => 'array']);
}
```

### DON'T ❌
```php
// ❌ Using env() directly
if (env('APP_ENV') === 'production') { // ❌ null when cached!
    // ...
}

// ❌ Hardcoding environment logic
if ($_SERVER['HTTP_HOST'] === 'example.com') { // ❌ Fragile
    // ...
}
```

---

## 5. Type Safety and Validation

Validate and cast config values to ensure type safety.

### DO ✅
```php
// config/app.php
return [
    'pagination' => [
        'per_page' => (int) env('APP_PAGINATION_PER_PAGE', 15),
        'max_per_page' => (int) env('APP_PAGINATION_MAX', 100),
    ],
    
    'features' => [
        'new_dashboard' => (bool) env('FEATURE_NEW_DASHBOARD', false),
        'beta_api' => (bool) env('FEATURE_BETA_API', false),
    ],
];

// Validate in service provider
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->validateConfig();
    }
    
    private function validateConfig(): void
    {
        // Validate required values
        if (empty(config('app.key'))) {
            throw new \RuntimeException('APP_KEY is required');
        }
        
        // Validate types
        $perPage = config('app.pagination.per_page');
        if (!is_int($perPage) || $perPage < 1) {
            throw new \RuntimeException('pagination.per_page must be a positive integer');
        }
        
        // Validate ranges
        $maxPerPage = config('app.pagination.max_per_page');
        if ($maxPerPage < $perPage) {
            throw new \RuntimeException('max_per_page must be >= per_page');
        }
    }
}
```

### Using Enums for Config Values

```php
// app/Enums/Environment.php
enum Environment: string
{
    case Local = 'local';
    case Staging = 'staging';
    case Production = 'production';
    
    public function isProduction(): bool
    {
        return $this === self::Production;
    }
}

// config/app.php
return [
    'environment' => Environment::from(env('APP_ENV', 'local')),
];

// Usage
$env = config('app.environment');
if ($env->isProduction()) {
    // ...
}
```

---

## 6. Config Caching and Performance

Config caching is critical for production performance.

### DO ✅
```bash
# Production deployment script
#!/bin/bash

# Clear and cache configs
php artisan config:clear
php artisan config:cache

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear and cache views
php artisan view:clear

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### What Config Caching Does

```php
// Without caching:
// - Every config() call reads from config files
// - env() calls executed every time
// - Slow in production

// With caching (php artisan config:cache):
// - All configs merged into bootstrap/cache/config.php
// - Single file load
// - env() calls replaced with actual values
// - Direct env() calls return null!
```

### When NOT to Cache Configs

```bash
# ❌ Don't cache in development
php artisan config:cache # Changes won't reflect until cleared!

# ✅ Only cache in production
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
fi
```

---

## 7. Config vs Constants vs Enums

Choose the right tool for the job.

### Decision Tree

```
Need to define a value?
│
├─ Changes per environment?
│  └─ Use config() + env()
│
├─ Fixed value, used in multiple places?
│  ├─ Related to a model/entity?
│  │  └─ Use class constants
│  │
│  ├─ Limited set of values (status, type)?
│  │  └─ Use PHP 8.1+ Enums
│  │
│  └─ Global constant?
│     └─ Use config() or define()
│
└─ Used only in one place?
   └─ Inline literal is fine
```

### Examples

```php
// ✅ Config - environment-specific
// config/services.php
'stripe' => [
    'key' => env('STRIPE_KEY'),
]

// ✅ Class constants - model-specific
class User extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_BANNED = 'banned';
    
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}

// ✅ Enums - limited set of values (Laravel 11+)
enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    
    public function canBeCancelled(): bool
    {
        return in_array($this, [self::Pending, self::Processing]);
    }
}

class Order extends Model
{
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
        ];
    }
}

// ❌ Don't use config for model-specific constants
// config/user.php
return [
    'status_active' => 'active', // ❌ Should be User::STATUS_ACTIVE
];

// ❌ Don't use magic strings
if ($user->status === 'active') { // ❌ Magic string
    // ...
}
```

---

## 8. Testing Configurations

Test your config validation and usage.

### DO ✅
```php
test('stripe config is required', function () {
    config(['services.stripe.key' => null]);
    
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Stripe key is required');
    
    app(AppServiceProvider::class)->boot();
});

test('pagination config has valid values', function () {
    config(['app.pagination.per_page' => 15]);
    config(['app.pagination.max_per_page' => 100]);
    
    $perPage = config('app.pagination.per_page');
    $maxPerPage = config('app.pagination.max_per_page');
    
    expect($perPage)->toBeInt()
        ->and($perPage)->toBeGreaterThan(0)
        ->and($maxPerPage)->toBeGreaterThanOrEqual($perPage);
});

test('environment detection works', function () {
    app()->detectEnvironment(fn () => 'production');
    
    expect(app()->isProduction())->toBeTrue()
        ->and(App::environment('production'))->toBeTrue();
});

// Integration test
test('payment service uses correct gateway', function () {
    // Production
    app()->detectEnvironment(fn () => 'production');
    $service = app(PaymentService::class);
    expect($service->getGateway())->toBeInstanceOf(LiveStripeGateway::class);
    
    // Local
    app()->detectEnvironment(fn () => 'local');
    $service = app(PaymentService::class);
    expect($service->getGateway())->toBeInstanceOf(MockStripeGateway::class);
});
```

---

## 9. Encrypted Environment Files

Never commit `.env` files with production secrets to version control.

### DO ✅
```bash
# Encrypt production env file
php artisan env:encrypt --env=production

# Creates .env.production.encrypted

# Decrypt for deployment
php artisan env:decrypt --env=production --key=$ENCRYPT_KEY

# Or use --readable for debugging (less secure)
php artisan env:encrypt --env=production --readable
```

### For Cloud Deployments

```bash
# AWS - Use Secrets Manager or Parameter Store
aws secretsmanager create-secret \
    --name production/laravel/env \
    --secret-string file://.env.production

# In deployment script
aws secretsmanager get-secret-value \
    --secret-id production/laravel/env \
    --query SecretString \
    --output text > .env

# Or use environment variables directly
export STRIPE_KEY=$(aws ssm get-parameter --name /prod/stripe_key --with-decryption --query Parameter.Value --output text)
```

### DON'T ❌
```bash
# ❌ Commit .env to git
git add .env
git commit -m "Add env file"
git push

# ❌ Share secrets in Slack/Email
# "Hey, the Stripe key is sk_live_abc123"

# ❌ Hardcode secrets in config files
// config/services.php
return [
    'stripe' => [
        'key' => 'sk_live_abc123', // ❌ Exposed in repo!
    ],
];
```

---

## 10. Dynamic Configuration

For configs that change at runtime, use database or cache.

### DO ✅
```php
// Database-backed settings
class Setting extends Model
{
    protected $fillable = ['key', 'value'];
    
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("settings.{$key}", 3600, function () use ($key, $default) {
            return self::where('key', $key)->value('value') ?? $default;
        });
    }
    
    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("settings.{$key}");
    }
}

// Usage
$siteName = Setting::get('site_name', 'My App');
Setting::set('maintenance_mode', true);

// Combine with config
class SettingsService
{
    public function get(string $key, mixed $default = null): mixed
    {
        // Try database first
        if ($value = Setting::get($key)) {
            return $value;
        }
        
        // Fallback to config
        return config("settings.{$key}", $default);
    }
}
```

---

## Summary: Configuration Checklist

Before deploying:

- [ ] All `env()` calls are in config files only
- [ ] Config files are logically organized
- [ ] Default values provided for optional configs
- [ ] Required configs validated in service provider
- [ ] Type casting applied to config values
- [ ] Environment-specific configs handled correctly
- [ ] Config cached in production (`php artisan config:cache`)
- [ ] Secrets encrypted or stored in vault
- [ ] No hardcoded secrets in code or version control
- [ ] Configs tested for validation and usage

---

## Anti-Patterns to Avoid

### 1. env() in Application Code
```php
// ❌ Returns null when config is cached
class PaymentService
{
    public function charge()
    {
        $key = env('STRIPE_KEY'); // ❌ null!
    }
}

// ✅ Use config()
class PaymentService
{
    public function charge()
    {
        $key = config('services.stripe.key');
    }
}
```

### 2. Hardcoded Configuration
```php
// ❌ Hardcoded values
class PostController
{
    public function index()
    {
        $posts = Post::paginate(15); // ❌ Magic number
        $timezone = 'UTC'; // ❌ Hardcoded
    }
}

// ✅ Use config
class PostController
{
    public function index()
    {
        $perPage = config('app.pagination.per_page', 15);
        $timezone = config('app.timezone', 'UTC');
        $posts = Post::paginate($perPage);
    }
}
```

### 3. Magic Strings
```php
// ❌ Magic strings scattered
if ($user->status === 'active') { }
if ($order->type === 'subscription') { }
if ($post->visibility === 'public') { }

// ✅ Use constants or enums
if ($user->status === User::STATUS_ACTIVE) { }
if ($order->type === OrderType::Subscription) { }
if ($post->visibility === PostVisibility::Public) { }
```

### 4. Config Bloat
```php
// ❌ Everything in one config file
// config/app.php
return [
    'stripe_key' => env('STRIPE_KEY'),
    'mailgun_secret' => env('MAILGUN_SECRET'),
    'pagination' => 15,
    'timezone' => 'UTC',
    // ... 100 more unrelated settings
];

// ✅ Organize by domain
// config/services.php - third-party services
// config/app.php - application settings
// config/features.php - feature flags
```

### 5. Ignoring Config Cache
```php
// ❌ Not caching in production
// Deployment script without config:cache

// ✅ Always cache in production
php artisan config:cache
php artisan route:cache
php artisan view:clear
```

### 6. Committing Secrets
```bash
# ❌ .env in git
git add .env
git commit -m "Add env"

# ✅ .env in .gitignore
echo ".env" >> .gitignore
git add .gitignore

# ✅ Use .env.example for template
cp .env .env.example
# Remove actual values from .env.example
git add .env.example
```