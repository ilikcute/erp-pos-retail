<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Product\ProductController;
use App\Http\Controllers\Api\Product\ProductBrandController;
use App\Http\Controllers\Api\Product\ProductCategoryController;

Route::prefix('product')->group(function () {

    // Brands
    Route::apiResource('brands', ProductBrandController::class);

    // Categories
    Route::get('categories/tree',    [ProductCategoryController::class, 'tree']);
    Route::apiResource('categories', ProductCategoryController::class);

    // Products
    Route::get('products/barcode/{barcode}', [ProductController::class, 'findByBarcode']);
    Route::post('products/{id}/variants',    [ProductController::class, 'addVariant']);
    Route::apiResource('products', ProductController::class);
});
