<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Pricing\PriceListController;
use App\Http\Controllers\Api\Pricing\PriceChangeRequestController;

Route::prefix('pricing')->group(function () {
    // Price Resolver & History
    Route::post('price-lists/resolve',          [PriceListController::class, 'resolve']);
    Route::get('price-lists/history/{variantId}', [PriceListController::class, 'priceHistory']);
    
    // Price List Items
    Route::get('price-lists/{id}/items',        [PriceListController::class, 'indexItems']);
    Route::post('price-lists/{id}/items',       [PriceListController::class, 'storeItem']);
    
    // Price Lists
    Route::apiResource('price-lists',           PriceListController::class);
    
    // Price Change Requests
    Route::post('price-change-requests/{id}/approve', [PriceChangeRequestController::class, 'approve']);
    Route::post('price-change-requests/{id}/reject',  [PriceChangeRequestController::class, 'reject']);
    Route::post('price-change-requests/{id}/apply',   [PriceChangeRequestController::class, 'apply']);
    Route::apiResource('price-change-requests',       PriceChangeRequestController::class);
});
