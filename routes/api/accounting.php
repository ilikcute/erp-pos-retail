<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Accounting\CoaController;
use App\Http\Controllers\Api\Accounting\PaymentMethodController;

Route::prefix('chart-of-accounts')->group(function () {
    Route::get('/', [CoaController::class, 'index']);
    Route::post('/', [CoaController::class, 'store']);
    Route::get('/{id}', [CoaController::class, 'show']);
    Route::put('/{id}', [CoaController::class, 'update']);
    Route::delete('/{id}', [CoaController::class, 'destroy']);
});

Route::prefix('payment-methods')->group(function () {
    Route::get('/', [PaymentMethodController::class, 'index']);
    Route::post('/', [PaymentMethodController::class, 'store']);
    Route::get('/{id}', [PaymentMethodController::class, 'show']);
    Route::put('/{id}', [PaymentMethodController::class, 'update']);
    Route::delete('/{id}', [PaymentMethodController::class, 'destroy']);
});
