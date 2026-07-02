<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/reporting', fn () => Inertia::render('Reporting/Index'));
