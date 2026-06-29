<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing Page
Route::middleware(['guest'])->group(function () {
    Route::get('/', fn() => Inertia::render('Welcome'))->name('welcome');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');

    // ── POS Web Module ──────────────────────────────────────────
    require __DIR__ . '/web/pos.php';

    // ── Product Web Module ──────────────────────────────────────
    require __DIR__ . '/web/product.php';

    // ── Inventory Web Module ────────────────────────────────────
    require __DIR__ . '/web/inventory.php';

    // ── Loyalty Web Module ──────────────────────────────────────
    require __DIR__ . '/web/loyalty.php';

    // ── Promotion Web Module ────────────────────────────────────
    require __DIR__ . '/web/promotion.php';

    // ── Accounting Web Module ───────────────────────────────────
    require __DIR__ . '/web/accounting.php';

    // ── System Web Module ───────────────────────────────────────
    require __DIR__ . '/web/system.php';

    // ── Purchasing Web Module ───────────────────────────────────
    require __DIR__ . '/web/purchasing.php';

    // ── Pricing Web Module ──────────────────────────────────────
    require __DIR__ . '/web/pricing.php';

    // ── Reporting Web Module ────────────────────────────────────
    require __DIR__ . '/web/reporting.php';

    // ── Master Data Web Module ──────────────────────────────────
    require __DIR__ . '/web/master-data.php';

    // ── User Profile Web Module ─────────────────────────────────
    require __DIR__ . '/web/profile.php';
});

require __DIR__ . '/auth.php';
