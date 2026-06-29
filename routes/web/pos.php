<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\POS\PosTransactionController;
use App\Http\Controllers\POS\SalesTransactionController;
use App\Http\Controllers\POS\CashierSessionController;
use App\Http\Controllers\POS\DayClosingController;
use App\Http\Controllers\POS\MonthClosingController;

Route::prefix('pos')->name('pos.')->group(function () {
    // Halaman utama POS
    Route::get('/', [PosTransactionController::class, 'index'])->name('index');
    Route::get('shifts/list', [SalesTransactionController::class, 'shiftsList'])->name('shifts.list');

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
    Route::post('shifts', [SalesTransactionController::class, 'storeShift'])->name('shifts.store');
    Route::put('shifts/{id}', [SalesTransactionController::class, 'updateShift'])->name('shifts.update');
    Route::delete('shifts/{id}', [SalesTransactionController::class, 'destroyShift'])->name('shifts.destroy');

    // Day & Month Closing Web Pages
    Route::get('day-closing', [SalesTransactionController::class, 'dayClosingView'])->name('day-closing.view');
    Route::get('month-closing', [SalesTransactionController::class, 'monthClosingView'])->name('month-closing.view');

    Route::get('sales', [SalesTransactionController::class, 'index'])->name('sales.index');
    Route::get('sales/{id}', [SalesTransactionController::class, 'show'])->name('sales.show');

    // Cashier Sessions
    Route::prefix('sessions')->name('sessions.')->group(function () {
        Route::get('/active', [CashierSessionController::class, 'active'])->name('active');
        Route::get('/', [CashierSessionController::class, 'index'])->name('index');
        Route::post('/open', [CashierSessionController::class, 'open'])->name('open');
        Route::post('/{id}/close', [CashierSessionController::class, 'close'])->name('close');
        Route::get('/{id}', [CashierSessionController::class, 'show'])->name('show');
    });

    // Day Closings
    Route::prefix('day-closings')->name('day-closings.')->group(function () {
        Route::get('/', [DayClosingController::class, 'index'])->name('index');
        Route::get('/today', [DayClosingController::class, 'todayStats'])->name('today');
        Route::get('/check', [DayClosingController::class, 'check'])->name('check');
        Route::post('/close', [DayClosingController::class, 'close'])->name('close');
        Route::get('/{id}', [DayClosingController::class, 'show'])->name('show');
    });

    // Month Closings
    Route::prefix('month-closings')->name('month-closings.')->group(function () {
        Route::get('/', [MonthClosingController::class, 'index'])->name('index');
        Route::get('/check', [MonthClosingController::class, 'check'])->name('check');
        Route::post('/close', [MonthClosingController::class, 'close'])->name('close');
        Route::get('/{year}/{month}', [MonthClosingController::class, 'show'])->name('show');
    });
});
