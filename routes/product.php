<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Product\ProductController;

Route::prefix('product')->group(function () {
    Route::get('products/barcode/{barcode}', [ProductController::class, 'findByBarcode']);
    Route::post('products/{id}/variants',    [ProductController::class, 'addVariant']);
    Route::apiResource('products',           ProductController::class);
});
