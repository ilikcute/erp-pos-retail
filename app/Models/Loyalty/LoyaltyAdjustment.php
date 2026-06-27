<?php

namespace App\Models\Loyalty;

use App\Enums\Loyalty\AdjustmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyAdjustment extends Model
{
    protected $fillable = [
        'adjustment_number',
        'loyalty_account_id',
        'adjustment_type',
        'points',
        'reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'adjustment_type' => AdjustmentType::class,
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(LoyaltyAccount::class, 'loyalty_account_id');
    }
}
