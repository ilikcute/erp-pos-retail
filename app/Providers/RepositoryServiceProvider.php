<?php

namespace App\Providers;

use App\Repositories\Contracts\Auth\UserAuthRepositoryInterface;
// Contracts & Implementations
// ── Auth & System ─────────────────────────────────────────────
use App\Repositories\Contracts\Inventory\InventoryLedgerRepositoryInterface;
use App\Repositories\Contracts\MasterData\CurrencyRepositoryInterface;
use App\Repositories\Contracts\MasterData\CustomerCategoryRepositoryInterface;
use App\Repositories\Contracts\MasterData\CustomerRepositoryInterface;
use App\Repositories\Contracts\MasterData\SupplierRepositoryInterface;
use App\Repositories\Contracts\MasterData\TaxRepositoryInterface;
use App\Repositories\Contracts\MasterData\UnitConversionRepositoryInterface;
use App\Repositories\Contracts\MasterData\UnitRepositoryInterface;
// ── MasterData ────────────────────────────────────────────────
use App\Repositories\Contracts\POS\SalesHoldRepositoryInterface;
use App\Repositories\Contracts\POS\SalesSessionRepositoryInterface;
use App\Repositories\Contracts\POS\SalesTransactionRepositoryInterface;
use App\Repositories\Contracts\POS\ShiftRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceChangeRequestRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceHistoryRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceListItemRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceListRepositoryInterface;
use App\Repositories\Contracts\Product\ProductBrandRepositoryInterface;
use App\Repositories\Contracts\Product\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\Product\ProductRepositoryInterface;
use App\Repositories\Contracts\Product\ProductVariantRepositoryInterface;
use App\Repositories\Contracts\System\PermissionRepositoryInterface;
use App\Repositories\Contracts\System\RoleRepositoryInterface;
// ── Product ───────────────────────────────────────────────────
use App\Repositories\Contracts\System\UserRepositoryInterface;
use App\Repositories\Eloquent\Auth\EloquentUserAuthRepository;
use App\Repositories\Eloquent\Inventory\EloquentInventoryLedgerRepository;
use App\Repositories\Eloquent\MasterData\EloquentCurrencyRepository;
use App\Repositories\Eloquent\MasterData\EloquentCustomerCategoryRepository;
use App\Repositories\Eloquent\MasterData\EloquentCustomerRepository;
use App\Repositories\Eloquent\MasterData\EloquentSupplierRepository;
use App\Repositories\Eloquent\MasterData\EloquentTaxRepository;
// ── Pricing ───────────────────────────────────────────────────
use App\Repositories\Eloquent\MasterData\EloquentUnitConversionRepository;
use App\Repositories\Eloquent\MasterData\EloquentUnitRepository;
use App\Repositories\Eloquent\POS\EloquentSalesHoldRepository;
use App\Repositories\Eloquent\POS\EloquentSalesSessionRepository;
use App\Repositories\Eloquent\POS\EloquentSalesTransactionRepository;
use App\Repositories\Eloquent\POS\EloquentShiftRepository;
use App\Repositories\Eloquent\Pricing\EloquentPriceChangeRequestRepository;
use App\Repositories\Eloquent\Pricing\EloquentPriceHistoryRepository;
// ── Inventory ─────────────────────────────────────────────────
use App\Repositories\Eloquent\Pricing\EloquentPriceListItemRepository;
use App\Repositories\Eloquent\Pricing\EloquentPriceListRepository;
// ── POS ───────────────────────────────────────────────────────
use App\Repositories\Contracts\Loyalty\AccountRepositoryInterface;
use App\Repositories\Contracts\Loyalty\TierRepositoryInterface;
use App\Repositories\Eloquent\Loyalty\AccountRepository;
use App\Repositories\Eloquent\Loyalty\TierRepository;
use App\Repositories\Eloquent\Product\EloquentProductBrandRepository;
use App\Repositories\Eloquent\Product\EloquentProductCategoryRepository;
use App\Repositories\Eloquent\Product\EloquentProductRepository;
use App\Repositories\Eloquent\Product\EloquentProductVariantRepository;
use App\Repositories\Eloquent\System\EloquentPermissionRepository;
use App\Repositories\Eloquent\System\EloquentRoleRepository;
use App\Repositories\Eloquent\System\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

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
        CurrencyRepositoryInterface::class => EloquentCurrencyRepository::class,
        UnitConversionRepositoryInterface::class => EloquentUnitConversionRepository::class,

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

        // Loyalty
        AccountRepositoryInterface::class => AccountRepository::class,
        TierRepositoryInterface::class => TierRepository::class,
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
