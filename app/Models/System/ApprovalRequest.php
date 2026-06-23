<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'reference_number',
        'approval_type_id',
        'requested_by',
        'current_approver_id',
        'current_level',
        'status',
        'entity_type',
        'entity_id',
        'amount',
        'notes',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function approvalType(): BelongsTo
    {
        return $this->belongsTo(ApprovalType::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function currentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class);
    }
}
