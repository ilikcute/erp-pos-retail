<?php

use App\Http\Controllers\Api\System\DocumentSequenceController;
use App\Http\Controllers\System\ActivityLogController;
use App\Http\Controllers\System\ApprovalController;
use App\Http\Controllers\System\AuditLogController;
use App\Http\Controllers\System\NotificationController;
use App\Http\Controllers\System\RoleController;
use App\Http\Controllers\System\SystemController;
use App\Http\Controllers\System\UserController;
use Illuminate\Support\Facades\Route;

// System pages
Route::get('/system', [SystemController::class, 'index'])->name('system.index');
Route::post('/system/settings', [SystemController::class, 'updateSettings'])->name('system.settings.update');
Route::post('/system/business-profile', [SystemController::class, 'updateBusinessProfile'])->name('system.business-profile.update');

Route::get('/system/users', [UserController::class, 'index'])->name('system.users.index');
Route::post('/system/users', [UserController::class, 'store'])->name('system.users.store');
Route::put('/system/users/{id}', [UserController::class, 'update'])->name('system.users.update');
Route::delete('/system/users/{id}', [UserController::class, 'destroy'])->name('system.users.destroy');

Route::get('/system/roles', [RoleController::class, 'index'])->name('system.roles.index');
Route::post('/system/roles', [RoleController::class, 'store'])->name('system.roles.store');
Route::put('/system/roles/{id}', [RoleController::class, 'update'])->name('system.roles.update');
Route::delete('/system/roles/{id}', [RoleController::class, 'destroy'])->name('system.roles.destroy');

// Approvals
Route::prefix('approvals')->group(function () {
    Route::get('/', [ApprovalController::class, 'index'])->name('system.approvals.index');
    Route::post('{id}/approve', [ApprovalController::class, 'approve'])->name('system.approvals.approve');
    Route::post('{id}/reject', [ApprovalController::class, 'reject'])->name('system.approvals.reject');
});

// Activity logs
Route::get('/system/activity-logs', [ActivityLogController::class, 'index'])->name('system.activity-logs.index');

// Audit logs
Route::get('/system/audit-logs', [AuditLogController::class, 'index'])->name('system.audit-logs.index');

// Notifications
Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('system.notifications.index');
    Route::post('{id}/read', [NotificationController::class, 'markAsRead'])->name('system.notifications.read');
    Route::post('read-all', [NotificationController::class, 'markAllAsRead'])->name('system.notifications.read-all');
});

// Document Sequences
Route::prefix('document-sequences')->group(function () {
    Route::get('/', [DocumentSequenceController::class, 'index']);
    Route::post('/', [DocumentSequenceController::class, 'store']);
    Route::put('{id}', [DocumentSequenceController::class, 'update']);
});
