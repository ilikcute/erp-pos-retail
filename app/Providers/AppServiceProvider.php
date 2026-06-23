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
        // ── Auth ──────────────────────────────────────────────────────
        $this->app->bind(UserAuthRepositoryInterface::class, EloquentUserAuthRepository::class);

        // ── System ────────────────────────────────────────────────────
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        // ── MasterData ────────────────────────────────────────────────
        $this->app->bind(SupplierRepositoryInterface::class, EloquentSupplierRepository::class);

        // ── Support (Singletons) ──────────────────────────────────────
        $this->app->singleton(AuditService::class);
        $this->app->singleton(DocumentNumberService::class);

        // ── Pricing (Singletons) ──────────────────────────────────────
        // PriceResolverService dipakai di POS setiap transaksi — singleton agar tidak re-instantiate
        $this->app->singleton(\App\Services\Pricing\PriceResolverService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
