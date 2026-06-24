<?php

namespace App\Models\POS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\System\User;

class SalesVoid extends Model
{
    protected $fillable = [
        'void_no',
        'sales_transaction_id',
        'status',
        'reason',
        'requested_by',
        'approved_by',
        'requested_at',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at'  => 'datetime',
    ];

    public function salesTransaction(): BelongsTo
    {
        return $this->belongsTo(SalesTransaction::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }
}
