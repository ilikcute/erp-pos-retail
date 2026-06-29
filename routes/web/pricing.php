<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pricing\PricingController;

Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
