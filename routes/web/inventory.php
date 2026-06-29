<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Api\Inventory\BalanceController;
use App\Http\Controllers\Api\Inventory\LedgerController;
use App\Http\Controllers\Api\Inventory\TransferController;
use App\Http\Controllers\Api\Inventory\AdjustmentController;
use App\Http\Controllers\Api\Inventory\OpnameController;
use App\Http\Controllers\Api\Inventory\PlanogramController;

Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('/inventory/transfer', [InventoryController::class, 'index'])->name('inventory.transfer');

Route::prefix('inventory')->group(function () {
    // Locations
    Route::post('locations', [InventoryController::class, 'storeLocation'])->name('inventory.locations.store');
    Route::put('locations/{id}', [InventoryController::class, 'updateLocation'])->name('inventory.locations.update');

    // Transfers
    Route::post('transfers', [InventoryController::class, 'storeTransfer'])->name('inventory.transfers.store');
    Route::post('transfers/{id}/post', [InventoryController::class, 'postTransfer'])->name('inventory.transfers.post');
    Route::post('transfers/{id}/cancel', [InventoryController::class, 'cancelTransfer'])->name('inventory.transfers.cancel');

    // Adjustments
    Route::post('adjustments', [InventoryController::class, 'storeAdjustment'])->name('inventory.adjustments.store');
    Route::post('adjustments/{id}/approve', [InventoryController::class, 'approveAdjustment'])->name('inventory.adjustments.approve');
    Route::post('adjustments/{id}/reject', [InventoryController::class, 'rejectAdjustment'])->name('inventory.adjustments.reject');

    // Opnames
    Route::post('opnames', [InventoryController::class, 'storeOpname'])->name('inventory.opnames.store');
    Route::put('opnames/{id}/count', [InventoryController::class, 'countOpname'])->name('inventory.opnames.count');
    Route::post('opnames/{id}/approve', [InventoryController::class, 'approveOpname'])->name('inventory.opnames.approve');
    Route::post('opnames/{id}/post', [InventoryController::class, 'postOpname'])->name('inventory.opnames.post');

    // Planograms
    Route::post('planograms', [InventoryController::class, 'storePlanogram'])->name('inventory.planograms.store');
    Route::put('planograms/{id}', [InventoryController::class, 'updatePlanogram'])->name('inventory.planograms.update');
    Route::delete('planograms/{id}', [InventoryController::class, 'destroyPlanogram'])->name('inventory.planograms.destroy');
});
