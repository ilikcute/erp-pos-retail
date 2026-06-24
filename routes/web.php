<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductBrandController;
use App\Http\Controllers\Product\ProductCategoryController;
use App\Http\Controllers\System\UserController;
use App\Http\Controllers\System\RoleController;
use App\Http\Controllers\POS\SalesTransactionController;
use App\Http\Controllers\MasterData\MasterDataController;
use App\Http\Controllers\Pricing\PricingController;
use App\Http\Controllers\Promotion\PromotionController;
use App\Http\Controllers\Loyalty\LoyaltyController;

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', fn () => redirect('/dashboard'));
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
    
    // Business module pages
    Route::get('/pos', fn () => Inertia::render('POS/Index'));
    Route::get('/pos/sales', [SalesTransactionController::class, 'index'])->name('pos.sales.index');
    Route::get('/pos/sales/{id}', [SalesTransactionController::class, 'show'])->name('pos.sales.show');
    
    // Product Module Routes
    Route::prefix('product')->name('product.')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
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
    Route::get('/inventory', fn () => Inertia::render('Inventory/Index', ['activeTab' => 'balance']));
    Route::get('/inventory/transfer', fn () => Inertia::render('Inventory/Index', ['activeTab' => 'transfers']));
    Route::get('/purchasing', fn () => redirect('/purchasing/po'));
    Route::get('/purchasing/po', fn () => Inertia::render('Purchasing/Index', ['activeTab' => 'orders']));
    Route::get('/purchasing/receipt', fn () => Inertia::render('Purchasing/Index', ['activeTab' => 'receipts']));
    Route::get('/purchasing/create', fn () => Inertia::render('Purchasing/Create'));
    Route::get('/purchasing/{id}', fn () => Inertia::render('Purchasing/Show'));
    Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('/loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
    Route::get('/accounting', fn () => redirect('/accounting/coa'));
    Route::get('/accounting/coa', fn () => Inertia::render('Accounting/Index', ['activeTab' => 'coa']));
    Route::get('/accounting/journals', fn () => Inertia::render('Accounting/Index', ['activeTab' => 'journals']));
    Route::get('/reporting', fn () => Inertia::render('Reporting/Index'));
    
    // Master data pages
    Route::get('/master-data', fn () => redirect('/master-data/suppliers'));
    Route::get('/master-data/suppliers', [MasterDataController::class, 'suppliers'])->name('master-data.suppliers');
    Route::get('/master-data/customers', [MasterDataController::class, 'customers'])->name('master-data.customers');
    
    // System pages
    Route::get('/system', fn () => Inertia::render('System/Index'));
    Route::get('/system/users', [UserController::class, 'index'])->name('system.users.index');
    Route::get('/system/roles', [RoleController::class, 'index'])->name('system.roles.index');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
