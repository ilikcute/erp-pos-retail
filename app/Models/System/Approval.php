<?php

namespace App\Models\System;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Approval extends Model
{
    protected $fillable = [
        'approval_number',
        'module',
        'approvable_type',
        'approvable_id',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'notes',
        'rejection_notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function requestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }
}
