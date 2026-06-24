<?php

namespace App\Models\POS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\System\User;
use App\Models\MasterData\Customer;
use App\Traits\HasCreatedBy;

class SalesReturn extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'return_no',
        'sales_transaction_id',
        'cashier_id',
        'customer_id',
        'return_date',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'reason',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'subtotal'     => 'decimal:2',
        'tax_amount'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'return_date'  => 'date',
        'posted_at'    => 'datetime',
    ];

    public function salesTransaction(): BelongsTo
    {
        return $this->belongsTo(SalesTransaction::class);
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
        return $this->hasMany(SalesReturnItem::class);
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'POSTED');
    }
}
