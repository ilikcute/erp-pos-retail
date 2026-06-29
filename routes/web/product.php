<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductCategoryController;
use App\Http\Controllers\Product\ProductBrandController;

Route::prefix('product')->name('product.')->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/import/template', [ProductController::class, 'downloadImportTemplate'])->name('products.import.template');
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/{id}/variants', [ProductController::class, 'addVariant'])->name('products.variants.store');
    Route::put('/products/{productId}/variants/{variantId}', [ProductController::class, 'updateVariant'])->name('products.variants.update');
    Route::delete('/products/{productId}/variants/{variantId}', [ProductController::class, 'deleteVariant'])->name('products.variants.destroy');

    Route::get('/categories', [ProductCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [ProductCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [ProductCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [ProductCategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/brands', [ProductBrandController::class, 'index'])->name('brands.index');
    Route::post('/brands', [ProductBrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{id}', [ProductBrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{id}', [ProductBrandController::class, 'destroy'])->name('brands.destroy');
});
