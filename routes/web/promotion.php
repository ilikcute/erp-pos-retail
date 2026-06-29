<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Promotion\PromotionController as WebPromotionController;
use App\Http\Controllers\Api\Promotion\PromotionController as ApiPromotionController;
use App\Http\Controllers\Api\Promotion\PromotionSettingController;
use App\Http\Controllers\Api\POS\PosPromotionController;

Route::prefix('promotions')->group(function () {
    Route::get('/', [WebPromotionController::class, 'index'])->name('promotions.index');
    Route::post('/', [ApiPromotionController::class, 'store'])->name('promotions.store');
    Route::put('/{id}', [ApiPromotionController::class, 'update'])->name('promotions.update');
    Route::get('/{id}', [ApiPromotionController::class, 'show'])->name('promotions.show');
    Route::post('/{id}/activate', [ApiPromotionController::class, 'activate'])->name('promotions.activate');
    Route::post('/{id}/deactivate', [ApiPromotionController::class, 'deactivate'])->name('promotions.deactivate');
    Route::post('/simulate', [ApiPromotionController::class, 'simulate'])->name('promotions.simulate');
});

Route::prefix('promotions/settings')->group(function () {
    Route::get('/', [PromotionSettingController::class, 'show'])->name('promotions.settings.show');
    Route::put('/', [PromotionSettingController::class, 'update'])->name('promotions.settings.update');
});

Route::get('pos/promotions/active', [PosPromotionController::class, 'active'])
    ->name('pos.promotions.active');
