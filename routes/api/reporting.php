<?php

use App\Http\Controllers\Api\Reporting\ReportingController;
use Illuminate\Support\Facades\Route;

Route::prefix('reports')->group(function () {
    Route::get('sales', [ReportingController::class, 'salesReport']);
    Route::get('inventory-valuation', [ReportingController::class, 'inventoryReport']);
    Route::get('financial', [ReportingController::class, 'financialReport']);
    Route::get('purchasing/orders', [ReportingController::class, 'purchaseReport']);
    Route::get('margin', [ReportingController::class, 'marginReport']);

    // Exports — GET so browser can directly trigger file download
    Route::get('sales/export', [ReportingController::class, 'exportSales']);
    Route::get('inventory-valuation/export', [ReportingController::class, 'exportInventory']);
    Route::get('financial/export', [ReportingController::class, 'exportFinancial']);
    Route::get('margin/export', [ReportingController::class, 'exportMargin']);
});

