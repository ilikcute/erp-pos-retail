<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\System\RoleController;
use App\Http\Controllers\Api\System\UserController;

Route::prefix('system')->group(function () {
    // Roles
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:system.role.manage');
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::put('/roles/{id}', [RoleController::class, 'update'])->middleware('permission:system.role.manage');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('permission:system.role.manage');

    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:system.user.manage');
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:system.user.manage');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('permission:system.user.manage');
});

