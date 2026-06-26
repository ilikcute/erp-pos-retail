<?php

use App\Http\Controllers\Api\Product\ProductBrandController;
use App\Http\Controllers\Api\Product\ProductCategoryController;
use App\Http\Controllers\Api\Product\ProductController;
use App\Http\Controllers\Api\Product\ProductImportController;
use Illuminate\Support\Facades\Route;

Route::prefix('product')->group(function () {

    // Brands
    Route::apiResource('brands', ProductBrandController::class);

    // Categories
    Route::get('categories/tree', [ProductCategoryController::class, 'tree']);
    Route::apiResource('categories', ProductCategoryController::class);

    // Product Import / Template
    Route::get('products/import/template', [ProductImportController::class, 'downloadTemplate']);
    Route::post('products/import', [ProductImportController::class, 'import']);

    // Products
    Route::get('products/barcode/{barcode}', [ProductController::class, 'findByBarcode']);
    Route::post('products/{id}/variants', [ProductController::class, 'addVariant']);
    Route::apiResource('products', ProductController::class);
});
