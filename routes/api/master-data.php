<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MasterData\SupplierController;
use App\Http\Controllers\Api\MasterData\CustomerController;
use App\Http\Controllers\Api\MasterData\CustomerCategoryController;
use App\Http\Controllers\Api\MasterData\UnitController;
use App\Http\Controllers\Api\MasterData\TaxController;

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
