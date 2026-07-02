<?php

namespace App\Models\POS;

use App\Models\MasterData\Customer;
use App\Models\System\User;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesHold extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'hold_no',
        'sales_session_id',
        'cashier_id',
        'customer_id',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'grand_total',
        'notes',
        'created_by',
        'updated_by',
        'held_at',
        'resumed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'held_at' => 'datetime',
        'resumed_at' => 'datetime',
    ];

    public function salesSession(): BelongsTo
    {
        return $this->belongsTo(SalesSession::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesHoldItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'HELD');
    }

    public function isHeld(): bool
    {
        return $this->status === 'HELD';
    }
}
