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
Route::post('/accounting/journals/{id}/post', [AccountingController::class, 'postJournal'])->name('accounting.journals.post');
Route::post('/accounting/journals/{id}/void', [AccountingController::class, 'voidJournal'])->name('accounting.journals.void');

Route::post('/accounting/fiscal-periods', [AccountingController::class, 'storeFiscalPeriod'])->name('accounting.fiscal-periods.store');
Route::post('/accounting/fiscal-periods/{id}/close', [AccountingController::class, 'closeFiscalPeriod'])->name('accounting.fiscal-periods.close');

Route::post('/accounting/journal-templates', [AccountingController::class, 'storeTemplate'])->name('accounting.journal-templates.store');
Route::delete('/accounting/journal-templates/{id}', [AccountingController::class, 'destroyTemplate'])->name('accounting.journal-templates.destroy');

Route::post('/accounting/rules', [AccountingController::class, 'storeRule'])->name('accounting.rules.store');

Route::post('/accounting/payment-methods', [AccountingController::class, 'storePaymentMethod'])->name('accounting.payment-methods.store');
Route::put('/accounting/payment-methods/{id}', [AccountingController::class, 'updatePaymentMethod'])->name('accounting.payment-methods.update');
Route::delete('/accounting/payment-methods/{id}', [AccountingController::class, 'destroyPaymentMethod'])->name('accounting.payment-methods.destroy');
