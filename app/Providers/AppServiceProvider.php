<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Contracts
use App\Repositories\Contracts\Auth\UserAuthRepositoryInterface;
use App\Repositories\Contracts\System\UserRepositoryInterface;
use App\Repositories\Contracts\MasterData\SupplierRepositoryInterface;

// Implementations
use App\Repositories\Eloquent\Auth\EloquentUserAuthRepository;
use App\Repositories\Eloquent\System\EloquentUserRepository;
use App\Repositories\Eloquent\MasterData\EloquentSupplierRepository;

// Support services
use App\Support\AuditService;
use App\Support\DocumentNumberService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ── Support (Singletons) ──────────────────────────────────────
        $this->app->singleton(AuditService::class);
        $this->app->singleton(DocumentNumberService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
