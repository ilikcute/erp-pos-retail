<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Loyalty\LoyaltyController;
use App\Http\Controllers\Api\Loyalty\AccountController;
use App\Http\Controllers\Api\Loyalty\RedemptionController;
use App\Http\Controllers\Api\Loyalty\AdjustmentController as AdjustmentControllerLoyalty;
use App\Http\Controllers\Api\Loyalty\ConfigurationController;
use App\Http\Controllers\Api\Loyalty\TierController;
use App\Http\Controllers\Api\Loyalty\RewardCatalogController;

Route::get('/loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
Route::post('/loyalty/accounts', [LoyaltyController::class, 'storeAccount'])->name('loyalty.accounts.store');
Route::post('/loyalty/adjustments', [LoyaltyController::class, 'storeAdjustment'])->name('loyalty.adjustments.store');
Route::post('/loyalty/redeem', [LoyaltyController::class, 'storeRedemption'])->name('loyalty.redeem.store');
Route::post('/loyalty/redemptions/{id}/approve', [LoyaltyController::class, 'approveRedemption'])->name('loyalty.redemptions.approve');
Route::post('/loyalty/redemptions/{id}/reject', [LoyaltyController::class, 'rejectRedemption'])->name('loyalty.redemptions.reject');
Route::post('/loyalty/configuration', [LoyaltyController::class, 'updateConfiguration'])->name('loyalty.configuration.update');

Route::post('/loyalty/rewards', [LoyaltyController::class, 'storeReward'])->name('loyalty.rewards.store');
Route::put('/loyalty/rewards/{id}', [LoyaltyController::class, 'updateReward'])->name('loyalty.rewards.update');
Route::delete('/loyalty/rewards/{id}', [LoyaltyController::class, 'destroyReward'])->name('loyalty.rewards.destroy');

Route::post('/loyalty/tiers', [LoyaltyController::class, 'storeTier'])->name('loyalty.tiers.store');
Route::put('/loyalty/tiers/{id}', [LoyaltyController::class, 'updateTier'])->name('loyalty.tiers.update');
Route::delete('/loyalty/tiers/{id}', [LoyaltyController::class, 'destroyTier'])->name('loyalty.tiers.destroy');

Route::prefix('loyalty')->name('loyalty.')->group(function () {
    // Akun & Riwayat
    Route::get('accounts/{customer_id}', [AccountController::class, 'show']);
    Route::get('accounts/{customer_id}/transactions', [AccountController::class, 'transactions']);

    // Redeem
    Route::post('redeem', [RedemptionController::class, 'redeem']);

    // Adjustment
    Route::post('adjustments', [AdjustmentControllerLoyalty::class, 'store']);

    // Konfigurasi
    Route::get('configuration', [ConfigurationController::class, 'show']);
    Route::put('configuration', [ConfigurationController::class, 'update']);

    // Tiers
    Route::get('tiers', [TierController::class, 'index']);
    Route::post('tiers', [TierController::class, 'store']);
    Route::put('tiers/{id}', [TierController::class, 'update']);

    // Reward Catalog
    Route::get('rewards', [RewardCatalogController::class, 'index']);
    Route::post('rewards', [RewardCatalogController::class, 'store']);
    Route::put('rewards/{id}', [RewardCatalogController::class, 'update']);
    Route::delete('rewards/{id}', [RewardCatalogController::class, 'destroy']);
});
