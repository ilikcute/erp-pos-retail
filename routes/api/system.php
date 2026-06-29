<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\System\UserController;
use App\Http\Controllers\Api\System\RoleController;
use App\Http\Controllers\Api\System\PermissionController;
use App\Http\Controllers\Api\System\SystemSettingController;
use App\Http\Controllers\Api\System\BusinessProfileController;
use App\Http\Controllers\Api\System\AuditLogController;

Route::prefix('system')->group(function () {
    // Users
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/roles', [UserController::class, 'assignRole']);
    Route::delete('users/{user}/roles/{role}', [UserController::class, 'removeRole']);

    // Roles & Permissions
    Route::apiResource('roles', RoleController::class);
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);

    // System Settings
    Route::get('settings',           [SystemSettingController::class, 'index']);
    Route::put('settings',           [SystemSettingController::class, 'updateBulk']);
    Route::get('settings/{key}',     [SystemSettingController::class, 'show']);
    Route::put('settings/{key}',     [SystemSettingController::class, 'update']);

    // Business Profile
    Route::get('business-profile',   [BusinessProfileController::class, 'show']);
    Route::put('business-profile',   [BusinessProfileController::class, 'update']);

    // Audit & Activity Logs
    Route::get('audit-logs',         [AuditLogController::class, 'index']);
    Route::get('audit-logs/{id}',    [AuditLogController::class, 'show']);
});
