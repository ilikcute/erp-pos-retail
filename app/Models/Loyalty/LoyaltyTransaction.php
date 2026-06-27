<?php

namespace App\Models\Loyalty;

use App\Enums\Loyalty\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LoyaltyTransaction extends Model
{
    protected $fillable = [
        'reference_number',
        'transaction_type',
        'loyalty_account_id',
        'points',
        'balance_before',
        'balance_after',
        'transaction_value',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
        'transaction_date',
    ];

    protected $casts = [
        'transaction_type' => TransactionType::class,
        'transaction_date' => 'datetime',
        'transaction_value' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(LoyaltyAccount::class, 'loyalty_account_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
