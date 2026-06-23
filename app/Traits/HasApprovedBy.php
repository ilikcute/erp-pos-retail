<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\System\User;

trait HasApprovedBy
{
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function markAsApproved(int $userId): void
    {
        $this->forceFill([
            'status'      => 'APPROVED',
            'approved_by' => $userId,
            'approved_at' => now(),
        ])->save();
    }

    public function markAsRejected(int $userId, string $reason): void
    {
        $this->forceFill([
            'status'          => 'DRAFT',
            'rejected_by'     => $userId,
            'rejected_at'     => now(),
            'rejection_reason' => $reason,
        ])->save();
    }
}
