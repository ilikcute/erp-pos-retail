<?php

use App\Http\Controllers\Loyalty\LoyaltyController;
use App\Http\Controllers\MasterData\MasterDataController;
use App\Http\Controllers\POS\PosTransactionController;
use App\Http\Controllers\POS\SalesTransactionController;
use App\Http\Controllers\Pricing\PricingController;
use App\Http\Controllers\Product\ProductBrandController;
use App\Http\Controllers\Product\ProductCategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Promotion\PromotionController;
use App\Http\Controllers\System\RoleController;
use App\Http\Controllers\System\SystemController;
use App\Http\Controllers\System\UserController;
use App\Http\Controllers\Api\Inventory\BalanceController;
use App\Http\Controllers\Api\Inventory\LedgerController;
use App\Http\Controllers\Api\Inventory\TransferController;
use App\Http\Controllers\Api\Inventory\AdjustmentController;
use App\Http\Controllers\Api\Inventory\OpnameController;
use App\Http\Controllers\Api\Inventory\PlanogramController;
use App\Http\Controllers\Api\Loyalty\AccountController;
use App\Http\Controllers\Api\Loyalty\RedemptionController;
use App\Http\Controllers\Api\Loyalty\AdjustmentController as AdjustmentControllerLoyalty;
use App\Http\Controllers\Api\Loyalty\ConfigurationController;
use App\Http\Controllers\Api\Loyalty\TierController;
use App\Http\Controllers\Api\Loyalty\RewardCatalogController;


use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing Page
Route::middleware(['guest'])->group(function () {
    Route::get('/', fn() => Inertia::render('Welcome'))->name('welcome');
});


Route::middleware(['auth'])->group(function () {
    // Route::get('/', fn () => redirect('/dashboard'));
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');

    Route::prefix('pos')->name('pos.')->group(function () {
        // Halaman utama POS
        Route::get('/', [PosTransactionController::class, 'index'])->name('index');

        // Cart operations (Inertia requests)
        Route::post('cart/add', [PosTransactionController::class, 'addToCart'])->name('cart.add');
        Route::patch('cart/{cartId}', [PosTransactionController::class, 'updateCart'])->name('cart.update');
        Route::delete('cart/{cartId}', [PosTransactionController::class, 'destroyCart'])->name('cart.destroy');
        Route::delete('cart', [PosTransactionController::class, 'clearCart'])->name('cart.clear');

        // Hold operations
        Route::post('hold', [PosTransactionController::class, 'hold'])->name('hold');
        Route::post('hold/{heldCartId}/recall', [PosTransactionController::class, 'recallHold'])->name('hold.recall');

        // Pricing preview (AJAX)
        Route::post('pricing-preview', [PosTransactionController::class, 'pricingPreview'])->name('pricing-preview');

        // Submit transaction
        Route::post('checkout', [PosTransactionController::class, 'store'])->name('checkout');

        // Shifts & Sales history
        Route::get('shifts', [SalesTransactionController::class, 'shifts'])->name('shifts.index');
        Route::get('sales', [SalesTransactionController::class, 'index'])->name('sales.index');
        Route::get('sales/{id}', [SalesTransactionController::class, 'show'])->name('sales.show');
    });

    // Product Module Routes
    Route::prefix('product')->name('product.')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/import/template', [ProductController::class, 'downloadImportTemplate'])->name('products.import.template');
        Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/{id}/variants', [ProductController::class, 'addVariant'])->name('products.variants.store');
        Route::put('/products/{productId}/variants/{variantId}', [ProductController::class, 'updateVariant'])->name('products.variants.update');
        Route::delete('/products/{productId}/variants/{variantId}', [ProductController::class, 'deleteVariant'])->name('products.variants.destroy');

        Route::get('/categories', [ProductCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [ProductCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{id}', [ProductCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [ProductCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/brands', [ProductBrandController::class, 'index'])->name('brands.index');
        Route::post('/brands', [ProductBrandController::class, 'store'])->name('brands.store');
        Route::put('/brands/{id}', [ProductBrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{id}', [ProductBrandController::class, 'destroy'])->name('brands.destroy');
    });

    Route::prefix('inventory')->group(function () {
        // === 7.2 Stok ===
        Route::get('balances', [BalanceController::class, 'index']);
        Route::get('ledgers', [LedgerController::class, 'index']);

        // === 7.3 Transfer ===
        Route::get('transfers', [TransferController::class, 'index']);
        Route::post('transfers', [TransferController::class, 'store']);
        Route::get('transfers/{id}', [TransferController::class, 'show']);
        Route::post('transfers/{id}/post', [TransferController::class, 'post']);
        Route::post('transfers/{id}/cancel', [TransferController::class, 'cancel']);

        // === 7.4 Adjustment ===
        Route::get('adjustments', [AdjustmentController::class, 'index']);
        Route::post('adjustments', [AdjustmentController::class, 'store']);
        Route::post('adjustments/{id}/approve', [AdjustmentController::class, 'approve']);
        Route::post('adjustments/{id}/reject', [AdjustmentController::class, 'reject']);

        // === 7.5 Stock Opname ===
        Route::get('opnames', [OpnameController::class, 'index']);
        Route::post('opnames', [OpnameController::class, 'store']);
        Route::put('opnames/{id}/count', [OpnameController::class, 'count']);
        Route::post('opnames/{id}/approve', [OpnameController::class, 'approve']);
        Route::post('opnames/{id}/post', [OpnameController::class, 'post']);

        // === 7.6 Planogram ===
        Route::apiResource('planograms', PlanogramController::class);
    });

    Route::prefix('loyalty')->name('loyalty.')->group(function () {
        // Akun & Riwayat
        Route::get('accounts/{customer_id}', [AccountController::class, 'show']);
        Route::get('accounts/{customer_id}/transactions', [AccountController::class, 'transactions']);

        // Redeem
        Route::post('redeem', [RedemptionController::class, 'redeem']);

        // Adjustment
        Route::post('adjustments', [AdjustmentController::class, 'store']);

        // Konfigurasi
        Route::get('configuration', [ConfigurationController::class, 'show']);
        Route::put('configuration', [ConfigurationController::class, 'update']);

        // Tiers
        Route::get('tiers', [TierController::class, 'index']);
        Route::post('tiers', [TierController::class, 'store']);
        Route::put('tiers/{id}', [TierController::class, 'update']);

        // Reward Catalog
        Route::get('rewards', [RewardCatalogController::class, 'index']);
        Route::post('rewards', [RewardCatalogController::class, 'store']);
        Route::put('rewards/{id}', [RewardCatalogController::class, 'update']);
        Route::delete('rewards/{id}', [RewardCatalogController::class, 'destroy']);
    });

    Route::prefix('chart-of-accounts')->group(function () {
        Route::get('/', [CoaController::class, 'index']);
        Route::post('/', [CoaController::class, 'store']);
        Route::get('/{id}', [CoaController::class, 'show']);
        Route::put('/{id}', [CoaController::class, 'update']);
        Route::delete('/{id}', [CoaController::class, 'destroy']);
    });

    Route::prefix('payment-methods')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'index']);
        Route::post('/', [PaymentMethodController::class, 'store']);
        Route::get('/{id}', [PaymentMethodController::class, 'show']);
        Route::put('/{id}', [PaymentMethodController::class, 'update']);
        Route::delete('/{id}', [PaymentMethodController::class, 'destroy']);
    });

    // Route::get('/inventory', fn() => Inertia::render('Inventory/Index', ['activeTab' => 'balance']));
    // Route::get('/inventory/transfer', fn() => Inertia::render('Inventory/Index', ['activeTab' => 'transfers']));
    Route::get('/purchasing', fn() => redirect('/purchasing/po'));
    Route::get('/purchasing/po', fn() => Inertia::render('Purchasing/Index', ['activeTab' => 'orders']));
    Route::get('/purchasing/receipt', fn() => Inertia::render('Purchasing/Index', ['activeTab' => 'receipts']));
    Route::get('/purchasing/create', fn() => Inertia::render('Purchasing/Create'));
    Route::get('/purchasing/{id}', fn() => Inertia::render('Purchasing/Show'));
    Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('/loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
    Route::get('/accounting', fn() => redirect('/accounting/coa'));
    Route::get('/accounting/coa', fn() => Inertia::render('Accounting/Index', ['activeTab' => 'coa']));
    Route::get('/accounting/journals', fn() => Inertia::render('Accounting/Index', ['activeTab' => 'journals']));
    Route::get('/reporting', fn() => Inertia::render('Reporting/Index'));

    // Master data pages
    Route::get('/master-data', fn() => redirect('/master-data/suppliers'));
    Route::get('/master-data/suppliers', [MasterDataController::class, 'suppliers'])->name('master-data.suppliers');
    Route::get('/master-data/customers', [MasterDataController::class, 'customers'])->name('master-data.customers');
    Route::get('/master-data/customer-categories', [MasterDataController::class, 'customerCategories'])->name('master-data.customer-categories');
    Route::get('/master-data/currencies', [MasterDataController::class, 'currencies'])->name('master-data.currencies');
    Route::get('/master-data/taxes', [MasterDataController::class, 'taxes'])->name('master-data.taxes');
    Route::get('/master-data/units', [MasterDataController::class, 'units'])->name('master-data.units');
    Route::get('/master-data/unit-conversions', [MasterDataController::class, 'unitConversions'])->name('master-data.unit-conversions');
    Route::get('/master-data/price-lists', [MasterDataController::class, 'priceLists'])->name('master-data.price-lists');

    // System pages
    Route::get('/system', [SystemController::class, 'index'])->name('system.index');

    Route::get('/system/users', [UserController::class, 'index'])->name('system.users.index');
    Route::post('/system/users', [UserController::class, 'store'])->name('system.users.store');
    Route::put('/system/users/{id}', [UserController::class, 'update'])->name('system.users.update');
    Route::delete('/system/users/{id}', [UserController::class, 'destroy'])->name('system.users.destroy');

    Route::get('/system/roles', [RoleController::class, 'index'])->name('system.roles.index');
    Route::post('/system/roles', [RoleController::class, 'store'])->name('system.roles.store');
    Route::put('/system/roles/{id}', [RoleController::class, 'update'])->name('system.roles.update');
    Route::delete('/system/roles/{id}', [RoleController::class, 'destroy'])->name('system.roles.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
