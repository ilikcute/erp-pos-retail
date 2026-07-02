<?php

use App\Http\Controllers\Pricing\PricingController;
use Illuminate\Support\Facades\Route;

Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
