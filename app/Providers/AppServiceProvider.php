<?php

namespace App\Providers;

use App\Repositories\Contracts\Accounting\CoaRepositoryInterface;
// Contracts
use App\Repositories\Contracts\Accounting\PaymentMethodRepositoryInterface;
use App\Repositories\Contracts\Auth\UserAuthRepositoryInterface;
use App\Repositories\Contracts\Inventory\BalanceRepositoryInterface;
// Implementations
use App\Repositories\Contracts\Inventory\LedgerRepositoryInterface;
use App\Repositories\Contracts\MasterData\SupplierRepositoryInterface;
use App\Repositories\Contracts\POS\DayClosingRepositoryInterface;
// Support services
use App\Repositories\Contracts\POS\MonthClosingRepositoryInterface;
use App\Repositories\Contracts\POS\SessionRepositoryInterface;
use App\Repositories\Contracts\Promotion\PromotionRepositoryInterface;
use App\Repositories\Contracts\System\UserRepositoryInterface;
use App\Repositories\Eloquent\Accounting\CoaRepository;
use App\Repositories\Eloquent\Accounting\PaymentMethodRepository;
use App\Repositories\Eloquent\Auth\EloquentUserAuthRepository;
use App\Repositories\Eloquent\Inventory\BalanceRepository;
use App\Repositories\Eloquent\Inventory\LedgerRepository;
use App\Repositories\Eloquent\MasterData\EloquentSupplierRepository;
use App\Repositories\Eloquent\POS\DayClosingRepository;
use App\Repositories\Eloquent\POS\MonthClosingRepository;
use App\Repositories\Eloquent\POS\SessionRepository;
use App\Repositories\Eloquent\Promotion\PromotionRepository;
use App\Repositories\Eloquent\System\EloquentUserRepository;
use App\Services\Pricing\PriceResolverService;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Support\ServiceProvider;

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
        $this->app->singleton(PriceResolverService::class);

        // Inventory

        $this->app->bind(
            BalanceRepositoryInterface::class,
            BalanceRepository::class
        );

        $this->app->bind(
            LedgerRepositoryInterface::class,
            LedgerRepository::class
        );

        $this->app->bind(
            CoaRepositoryInterface::class,
            CoaRepository::class
        );

        $this->app->bind(
            PaymentMethodRepositoryInterface::class,
            PaymentMethodRepository::class
        );

        $this->app->bind(
            PromotionRepositoryInterface::class,
            PromotionRepository::class
        );

        $this->app->bind(
            SessionRepositoryInterface::class,
            SessionRepository::class
        );

        $this->app->bind(
            DayClosingRepositoryInterface::class,
            DayClosingRepository::class
        );

        // ═══════════════════════════════════════════════════════════
        // MONTH CLOSING
        // ═══════════════════════════════════════════════════════════
        $this->app->bind(
            MonthClosingRepositoryInterface::class,
            MonthClosingRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
