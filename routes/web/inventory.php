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
