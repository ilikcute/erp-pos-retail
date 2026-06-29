<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\POS\ShiftController;
use App\Http\Controllers\Api\POS\SalesSessionController;
use App\Http\Controllers\Api\POS\SalesTransactionController;
use App\Http\Controllers\Api\POS\SalesHoldController;

Route::prefix('pos')->group(function () {
    // ── Shifts ──────────────────────────────────────────────
    Route::apiResource('shifts', ShiftController::class);
    Route::get('shifts-active', [ShiftController::class, 'active']);

    // ── Sales Sessions ──────────────────────────────────────
    Route::post('sessions/open', [SalesSessionController::class, 'open']);
    Route::get('sessions/my-open', [SalesSessionController::class, 'myOpenSession']);
    Route::post('sessions/{id}/close', [SalesSessionController::class, 'close']);
    Route::apiResource('sessions', SalesSessionController::class)->only(['index', 'show']);

    // ── Sales Transactions ──────────────────────────────────
    Route::post('transactions/{id}/void', [SalesTransactionController::class, 'void']);
    Route::get('transactions/session/{sessionId}', [SalesTransactionController::class, 'bySession']);
    Route::apiResource('transactions', SalesTransactionController::class)->only(['index', 'show', 'store']);

    // ── Hold Bills ──────────────────────────────────────────
    Route::get('sessions/{sessionId}/holds', [SalesHoldController::class, 'index']);
    Route::post('holds', [SalesHoldController::class, 'store']);
    Route::get('holds/{id}', [SalesHoldController::class, 'show']);
    Route::post('holds/{id}/resume', [SalesHoldController::class, 'resume']);
    Route::post('holds/{id}/cancel', [SalesHoldController::class, 'cancel']);
});
