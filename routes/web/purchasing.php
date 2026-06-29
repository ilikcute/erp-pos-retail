<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Purchasing\PurchasingController;

Route::get('/purchasing', fn() => redirect()->route('purchasing.po'));
Route::get('/purchasing/po', [PurchasingController::class, 'index'])->name('purchasing.po');
Route::get('/purchasing/receipt', [PurchasingController::class, 'index'])->name('purchasing.receipt');
Route::get('/purchasing/create', [PurchasingController::class, 'create'])->name('purchasing.create');
Route::post('/purchasing', [PurchasingController::class, 'store'])->name('purchasing.store');
Route::get('/purchasing/{id}', [PurchasingController::class, 'show'])->name('purchasing.show');
Route::post('/purchasing/{id}/approve', [PurchasingController::class, 'approve'])->name('purchasing.approve');
Route::post('/purchasing/{id}/cancel', [PurchasingController::class, 'cancel'])->name('purchasing.cancel');
