<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Contracts & Implementations
// ── Auth & System ─────────────────────────────────────────────
use App\Repositories\Contracts\Auth\UserAuthRepositoryInterface;
use App\Repositories\Eloquent\Auth\EloquentUserAuthRepository;
use App\Repositories\Contracts\System\UserRepositoryInterface;
use App\Repositories\Eloquent\System\EloquentUserRepository;
use App\Repositories\Contracts\System\RoleRepositoryInterface;
use App\Repositories\Eloquent\System\EloquentRoleRepository;
use App\Repositories\Contracts\System\PermissionRepositoryInterface;
use App\Repositories\Eloquent\System\EloquentPermissionRepository;

// ── MasterData ────────────────────────────────────────────────
use App\Repositories\Contracts\MasterData\SupplierRepositoryInterface;
use App\Repositories\Eloquent\MasterData\EloquentSupplierRepository;
use App\Repositories\Contracts\MasterData\CustomerRepositoryInterface;
use App\Repositories\Eloquent\MasterData\EloquentCustomerRepository;
use App\Repositories\Contracts\MasterData\CustomerCategoryRepositoryInterface;
use App\Repositories\Eloquent\MasterData\EloquentCustomerCategoryRepository;
use App\Repositories\Contracts\MasterData\UnitRepositoryInterface;
use App\Repositories\Eloquent\MasterData\EloquentUnitRepository;
use App\Repositories\Contracts\MasterData\TaxRepositoryInterface;
use App\Repositories\Eloquent\MasterData\EloquentTaxRepository;

// ── Product ───────────────────────────────────────────────────
use App\Repositories\Contracts\Product\ProductBrandRepositoryInterface;
use App\Repositories\Eloquent\Product\EloquentProductBrandRepository;
use App\Repositories\Contracts\Product\ProductCategoryRepositoryInterface;
use App\Repositories\Eloquent\Product\EloquentProductCategoryRepository;
use App\Repositories\Contracts\Product\ProductRepositoryInterface;
use App\Repositories\Eloquent\Product\EloquentProductRepository;
use App\Repositories\Contracts\Product\ProductVariantRepositoryInterface;
use App\Repositories\Eloquent\Product\EloquentProductVariantRepository;

// ── Pricing ───────────────────────────────────────────────────
use App\Repositories\Contracts\Pricing\PriceListRepositoryInterface;
use App\Repositories\Eloquent\Pricing\EloquentPriceListRepository;
use App\Repositories\Contracts\Pricing\PriceListItemRepositoryInterface;
use App\Repositories\Eloquent\Pricing\EloquentPriceListItemRepository;
use App\Repositories\Contracts\Pricing\PriceChangeRequestRepositoryInterface;
use App\Repositories\Eloquent\Pricing\EloquentPriceChangeRequestRepository;
use App\Repositories\Contracts\Pricing\PriceHistoryRepositoryInterface;
use App\Repositories\Eloquent\Pricing\EloquentPriceHistoryRepository;

// ── Inventory ─────────────────────────────────────────────────
use App\Repositories\Contracts\Inventory\InventoryLedgerRepositoryInterface;
use App\Repositories\Eloquent\Inventory\EloquentInventoryLedgerRepository;

// ── POS ───────────────────────────────────────────────────────
use App\Repositories\Contracts\POS\ShiftRepositoryInterface;
use App\Repositories\Eloquent\POS\EloquentShiftRepository;
use App\Repositories\Contracts\POS\SalesSessionRepositoryInterface;
use App\Repositories\Eloquent\POS\EloquentSalesSessionRepository;
use App\Repositories\Contracts\POS\SalesTransactionRepositoryInterface;
use App\Repositories\Eloquent\POS\EloquentSalesTransactionRepository;
use App\Repositories\Contracts\POS\SalesHoldRepositoryInterface;
use App\Repositories\Eloquent\POS\EloquentSalesHoldRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    protected array $repositories = [
        // Auth & System
        UserAuthRepositoryInterface::class => EloquentUserAuthRepository::class,
        UserRepositoryInterface::class => EloquentUserRepository::class,
        RoleRepositoryInterface::class => EloquentRoleRepository::class,
        PermissionRepositoryInterface::class => EloquentPermissionRepository::class,

        // MasterData
        SupplierRepositoryInterface::class => EloquentSupplierRepository::class,
        CustomerRepositoryInterface::class => EloquentCustomerRepository::class,
        CustomerCategoryRepositoryInterface::class => EloquentCustomerCategoryRepository::class,
        UnitRepositoryInterface::class => EloquentUnitRepository::class,
        TaxRepositoryInterface::class => EloquentTaxRepository::class,

        // Product
        ProductBrandRepositoryInterface::class => EloquentProductBrandRepository::class,
        ProductCategoryRepositoryInterface::class => EloquentProductCategoryRepository::class,
        ProductRepositoryInterface::class => EloquentProductRepository::class,
        ProductVariantRepositoryInterface::class => EloquentProductVariantRepository::class,

        // Pricing
        PriceListRepositoryInterface::class => EloquentPriceListRepository::class,
        PriceListItemRepositoryInterface::class => EloquentPriceListItemRepository::class,
        PriceChangeRequestRepositoryInterface::class => EloquentPriceChangeRequestRepository::class,
        PriceHistoryRepositoryInterface::class => EloquentPriceHistoryRepository::class,

        // Inventory
        InventoryLedgerRepositoryInterface::class => EloquentInventoryLedgerRepository::class,

        // POS
        ShiftRepositoryInterface::class => EloquentShiftRepository::class,
        SalesSessionRepositoryInterface::class => EloquentSalesSessionRepository::class,
        SalesTransactionRepositoryInterface::class => EloquentSalesTransactionRepository::class,
        SalesHoldRepositoryInterface::class => EloquentSalesHoldRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->repositories as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }
    }

    public function boot(): void
    {
        //
    }
}
