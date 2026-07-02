<?php

use App\Http\Controllers\MasterData\MasterDataController;
use Illuminate\Support\Facades\Route;

Route::get('/master-data', fn () => redirect('/master-data/suppliers'));
Route::get('/master-data/suppliers', [MasterDataController::class, 'suppliers'])->name('master-data.suppliers');
Route::get('/master-data/customers', [MasterDataController::class, 'customers'])->name('master-data.customers');
Route::get('/master-data/customer-categories', [MasterDataController::class, 'customerCategories'])->name('master-data.customer-categories');
Route::get('/master-data/currencies', [MasterDataController::class, 'currencies'])->name('master-data.currencies');
Route::get('/master-data/taxes', [MasterDataController::class, 'taxes'])->name('master-data.taxes');
Route::get('/master-data/units', [MasterDataController::class, 'units'])->name('master-data.units');
Route::get('/master-data/unit-conversions', [MasterDataController::class, 'unitConversions'])->name('master-data.unit-conversions');
Route::get('/master-data/price-lists', [MasterDataController::class, 'priceLists'])->name('master-data.price-lists');
