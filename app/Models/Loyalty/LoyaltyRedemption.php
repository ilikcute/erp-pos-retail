<?php

namespace App\Models\Loyalty;

use App\Enums\Loyalty\RedemptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyRedemption extends Model
{
    protected $fillable = [
        'redemption_number',
        'loyalty_account_id',
        'reward_catalog_id',
        'points_used',
        'reward_value',
        'status',
        'voucher_code',
        'voucher_expiry',
        'notes',
        'approved_by',
        'approved_at',
        'rejection_notes',
    ];

    protected $casts = [
        'status' => RedemptionStatus::class,
        'voucher_expiry' => 'date',
        'approved_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(LoyaltyAccount::class, 'loyalty_account_id');
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(LoyaltyRewardCatalog::class, 'reward_catalog_id');
    }
}
