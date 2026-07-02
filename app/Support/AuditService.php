<?php

namespace App\Support;

use App\Models\System\ActivityLog;
use App\Models\System\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * Catat perubahan data (audit log).
     * Digunakan untuk create/update/delete data penting.
     */
    public function log(
        string $module,
        string $action,
        string $tableName,
        int|string $recordId,
        array $oldValues = [],
        array $newValues = [],
        ?string $documentNo = null,
        ?string $documentType = null,
        ?string $statusBefore = null,
        ?string $statusAfter = null,
        ?string $reason = null,
    ): void {
        AuditLog::create([
            'user_id' => auth()->id(),
            'module' => $module,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'document_no' => $documentNo,
            'document_type' => $documentType,
            'status_before' => $statusBefore,
            'status_after' => $statusAfter,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Catat aktivitas user (activity log).
     * Digunakan untuk login, logout, view report, export, dsb.
     */
    public function activity(
        string $activity,
        string $module,
        ?string $description = null,
    ): void {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity' => $activity,
            'module' => $module,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Helper: audit perubahan model (diff old vs new).
     */
    public function logModelChange(
        Model $model,
        string $module,
        string $action,
        array $originalValues = [],
    ): void {
        $this->log(
            module: $module,
            action: $action,
            tableName: $model->getTable(),
            recordId: $model->getKey(),
            oldValues: $originalValues,
            newValues: $model->getDirty(),
        );
    }
}
