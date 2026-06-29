<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Purchasing\PurchasingController;

Route::get('/purchasing', [PurchasingController::class, 'index'])->name('purchasing.index');
Route::get('/purchasing/po', fn() => redirect()->route('purchasing.index', ['activeTab' => 'orders']));
Route::get('/purchasing/receipt', fn() => redirect()->route('purchasing.index', ['activeTab' => 'receipts']));
Route::get('/purchasing/create', [PurchasingController::class, 'create'])->name('purchasing.create');
Route::post('/purchasing', [PurchasingController::class, 'store'])->name('purchasing.store');
Route::get('/purchasing/{id}', [PurchasingController::class, 'show'])->name('purchasing.show');

// Actions for PR
Route::post('/purchasing/requests', [PurchasingController::class, 'storeRequest'])->name('purchasing.requests.store');
Route::post('/purchasing/requests/{id}/approve', [PurchasingController::class, 'approveRequest'])->name('purchasing.requests.approve');
Route::post('/purchasing/requests/{id}/reject', [PurchasingController::class, 'rejectRequest'])->name('purchasing.requests.reject');

// Actions for PO
Route::post('/purchasing/{id}/approve', [PurchasingController::class, 'approve'])->name('purchasing.approve');
Route::post('/purchasing/{id}/cancel', [PurchasingController::class, 'cancel'])->name('purchasing.cancel');

// Actions for GR
Route::post('/purchasing/receipts', [PurchasingController::class, 'storeReceipt'])->name('purchasing.receipts.store');
Route::post('/purchasing/receipts/{id}/post', [PurchasingController::class, 'postReceipt'])->name('purchasing.receipts.post');

// Actions for Invoice
Route::post('/purchasing/invoices', [PurchasingController::class, 'storeInvoice'])->name('purchasing.invoices.store');
Route::post('/purchasing/invoices/{id}/post', [PurchasingController::class, 'postInvoice'])->name('purchasing.invoices.post');

// Actions for Payment
Route::post('/purchasing/payments', [PurchasingController::class, 'storePayment'])->name('purchasing.payments.store');
Route::post('/purchasing/payments/{id}/post', [PurchasingController::class, 'postPayment'])->name('purchasing.payments.post');

// Actions for Return
Route::post('/purchasing/returns', [PurchasingController::class, 'storeReturn'])->name('purchasing.returns.store');
Route::post('/purchasing/returns/{id}/post', [PurchasingController::class, 'postReturn'])->name('purchasing.returns.post');

// Actions for Landed Cost
Route::post('/purchasing/landed-costs', [PurchasingController::class, 'storeLandedCost'])->name('purchasing.landed-costs.store');

// Actions for Supplier Performance
Route::post('/purchasing/suppliers/{supplier_id}/performance', [PurchasingController::class, 'storeSupplierPerformance'])->name('purchasing.performance.store');
