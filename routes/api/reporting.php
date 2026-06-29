<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Reporting\ReportingController;

Route::prefix('reports')->group(function () {
    Route::get('sales', [ReportingController::class, 'salesReport']);
    Route::get('inventory-valuation', [ReportingController::class, 'inventoryReport']);
    Route::get('financial', [ReportingController::class, 'financialReport']);
    Route::get('purchasing/orders', [ReportingController::class, 'purchaseReport']);
    
    // Exports
    Route::post('sales/export', [ReportingController::class, 'exportSales']);
    Route::post('inventory/export', [ReportingController::class, 'exportInventory']);
    Route::post('financial/export', [ReportingController::class, 'exportFinancial']);
});
