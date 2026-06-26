<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\System\UserController;
use App\Http\Controllers\Api\System\RoleController;
use App\Http\Controllers\Api\System\SystemSettingController;
use App\Http\Controllers\Api\System\BusinessProfileController;
use App\Http\Controllers\Api\System\AuditLogController;
use App\Http\Controllers\Api\MasterData\SupplierController;
use App\Http\Controllers\Api\MasterData\CustomerController;
use App\Http\Controllers\Api\MasterData\CustomerCategoryController;
use App\Http\Controllers\Api\MasterData\UnitController;
use App\Http\Controllers\Api\MasterData\TaxController;


// Phase 2 — Product & Pricing
use App\Http\Controllers\Api\Product\ProductBrandController;
use App\Http\Controllers\Api\Product\ProductCategoryController;
use App\Http\Controllers\Api\Product\ProductController;
use App\Http\Controllers\Api\Pricing\PriceListController;
use App\Http\Controllers\Api\Pricing\PriceChangeRequestController;


/*
|--------------------------------------------------------------------------
| API Routes — Phase 1: Auth, System, MasterData
|           — Phase 2: Product & Pricing
|--------------------------------------------------------------------------
|
| Prefix  : /api/v1
| Auth    : Laravel Sanctum (Bearer token)
| Pattern : {module}.{resource}.{action}
|
*/

Route::prefix('v1')->group(function () {

    // ════════════════════════════════════════════════════════════════
    // AUTH — Public (no token required)
    // ════════════════════════════════════════════════════════════════
    Route::prefix('auth')->group(function () {
        Route::post('login',  [AuthController::class, 'login']);
    });

    // ════════════════════════════════════════════════════════════════
    // AUTHENTICATED ROUTES
    // ════════════════════════════════════════════════════════════════
    Route::middleware('auth:sanctum')->group(function () {

        // ── Auth (protected) ─────────────────────────────────────────
        Route::prefix('auth')->group(function () {
            Route::post('logout',         [AuthController::class, 'logout']);
            Route::post('logout-all',     [AuthController::class, 'logoutAllDevices']);
            Route::get('me',              [AuthController::class, 'me']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
        });

        // ── System ───────────────────────────────────────────────────
        Route::prefix('system')->group(function () {

            // Users
            Route::apiResource('users', UserController::class);

            // Roles & Permissions
            Route::apiResource('roles', RoleController::class);
            Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);

            // System Settings
            Route::get('settings',           [SystemSettingController::class, 'index']);
            Route::put('settings',           [SystemSettingController::class, 'updateBulk']);
            Route::get('settings/{key}',     [SystemSettingController::class, 'show']);
            Route::put('settings/{key}',     [SystemSettingController::class, 'update']);

            // Business Profile
            Route::get('business-profile',   [BusinessProfileController::class, 'show']);
            Route::put('business-profile',   [BusinessProfileController::class, 'update']);

            // Audit & Activity Logs
            Route::get('audit-logs',         [AuditLogController::class, 'index']);
            Route::get('audit-logs/{id}',    [AuditLogController::class, 'show']);
        });

        // ── MasterData ───────────────────────────────────────────────
        Route::prefix('master-data')->group(function () {

            // Supplier
            Route::apiResource('suppliers', SupplierController::class);

            // Customer Category
            Route::apiResource('customer-categories', CustomerCategoryController::class);

            // Customer
            Route::apiResource('customers', CustomerController::class);

            // Unit
            Route::apiResource('units', UnitController::class);

            // Tax
            Route::apiResource('taxes', TaxController::class);
        });

        // ── Phase 2: Product & Pricing ───────────────────────────────
        require __DIR__.'/product.php';
        require __DIR__.'/pricing.php';

        // ── Phase 3: POS ────────────────────────────────────────────
        require __DIR__.'/pos.php';

        // ── Reporting ───────────────────────────────────────────────
        Route::get('dashboard', [\App\Http\Controllers\Api\System\DashboardController::class, '__invoke']);
        require __DIR__.'/reporting.php';
    }); // end auth:sanctum
}); // end v1