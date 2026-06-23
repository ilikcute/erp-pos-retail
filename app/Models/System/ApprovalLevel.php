<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalLevel extends Model
{
    protected $fillable = [
        'approval_type_id',
        'level_order',
        'role_id',
        'min_amount',
        'max_amount',
        'is_active'
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'min_amount' => 'decimal:2', 'max_amount' => 'decimal:2'];
    }

    public function approvalType(): BelongsTo
    {
        return $this->belongsTo(ApprovalType::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
