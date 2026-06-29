<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Accounting\AccountingController;

// Web interface
Route::get('/accounting', [AccountingController::class, 'index'])->name('accounting.index');
Route::get('/accounting/coa', [AccountingController::class, 'index'])->name('accounting.coa');
Route::get('/accounting/journals', [AccountingController::class, 'index'])->name('accounting.journals');

// Form submissions (Inertia postbacks)
Route::post('/accounting/coa', [AccountingController::class, 'storeCoa'])->name('accounting.coa.store');
Route::put('/accounting/coa/{id}', [AccountingController::class, 'updateCoa'])->name('accounting.coa.update');
Route::delete('/accounting/coa/{id}', [AccountingController::class, 'destroyCoa'])->name('accounting.coa.destroy');

Route::post('/accounting/journals', [AccountingController::class, 'storeJournal'])->name('accounting.journals.store');

Route::post('/accounting/payment-methods', [AccountingController::class, 'storePaymentMethod'])->name('accounting.payment-methods.store');
Route::put('/accounting/payment-methods/{id}', [AccountingController::class, 'updatePaymentMethod'])->name('accounting.payment-methods.update');
Route::delete('/accounting/payment-methods/{id}', [AccountingController::class, 'destroyPaymentMethod'])->name('accounting.payment-methods.destroy');
