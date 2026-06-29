<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Reporting\{
    SalesReportController,
    InventoryReportController,
    FinancialReportController,
    PurchaseReportController,
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Prefix  : /api/v1
| Auth    : Laravel Sanctum (Bearer token)
|
*/

Route::prefix('v1')->group(function () {

    // ── AUTH (Public) ────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('login',  [AuthController::class, 'login']);
    });

    // ── AUTHENTICATED ROUTES ─────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // ── Auth (Protected) ─────────────────────────────────────────
        Route::prefix('auth')->group(function () {
            Route::post('logout',         [AuthController::class, 'logout']);
            Route::post('logout-all',     [AuthController::class, 'logoutAllDevices']);
            Route::get('me',              [AuthController::class, 'me']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
        });

        // ── System API Module ────────────────────────────────────────
        require __DIR__ . '/api/system.php';

        // ── MasterData API Module ────────────────────────────────────
        require __DIR__ . '/api/master-data.php';

        // ── Product API Module ───────────────────────────────────────
        require __DIR__ . '/api/product.php';

        // ── Pricing API Module ───────────────────────────────────────
        require __DIR__ . '/api/pricing.php';

        // ── POS API Module ───────────────────────────────────────────
        require __DIR__ . '/api/pos.php';

        // ── Reporting API Module (Standard) ──────────────────────────
        require __DIR__ . '/api/reporting.php';

        // ── Accounting API Module ────────────────────────────────────
        require __DIR__ . '/api/accounting.php';

        // ── Dashboard Route ──────────────────────────────────────────
        Route::get('dashboard', [\App\Http\Controllers\Api\System\DashboardController::class, '__invoke']);

        // ── Custom Reports ───────────────────────────────────────────
        // Sales Reports
        Route::prefix('sales')->group(function () {
            Route::get('/', [SalesReportController::class, 'index']);
            Route::get('/top-products', [SalesReportController::class, 'topProducts']);
            Route::get('/hourly-pattern', [SalesReportController::class, 'hourlyPattern']);
        });

        // Inventory Reports
        Route::prefix('inventory')->group(function () {
            Route::get('/stock-card', [InventoryReportController::class, 'stockCard']);
            Route::get('/movement', [InventoryReportController::class, 'movement']);
            Route::get('/valuation', [InventoryReportController::class, 'valuation']);
            Route::get('/low-stock', [InventoryReportController::class, 'lowStock']);
        });

        // Financial Reports
        Route::prefix('financial')->group(function () {
            Route::post('/generate', [FinancialReportController::class, 'generate']);
        });

        // Purchase Reports
        Route::prefix('purchasing')->group(function () {
            Route::get('/orders', [PurchaseReportController::class, 'orders']);
            Route::get('/payables', [PurchaseReportController::class, 'payables']);
            Route::get('/supplier-performance', [PurchaseReportController::class, 'supplierPerformance']);
        });
    });
});