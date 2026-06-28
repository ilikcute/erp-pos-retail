<?php

namespace App\Models\POS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\System\User;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Tax;
use App\Traits\HasCreatedBy;

class SalesTransaction extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'transaction_no',
        'sales_session_id',
        'cashier_id',
        'customer_id',
        'transaction_date',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'grand_total',
        'paid_amount',
        'change_amount',
        'tax_id',
        'tax_rate',
        'notes',
        'created_by',
        'updated_by',
        'posted_by',
        'voided_by',
        'posted_at',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'grand_total'     => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'change_amount'   => 'decimal:2',
        'tax_rate'        => 'decimal:2',
        'transaction_date' => 'date',
        'posted_at'       => 'datetime',
        'voided_at'       => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(CashierSession::class, 'cashier_session_id');
    }

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

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesTransactionItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalesPayment::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(SalesDiscount::class);
    }

    public function void(): HasOne
    {
        return $this->hasOne(SalesVoid::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'POSTED');
    }

    public function scopeVoided($query)
    {
        return $query->where('status', 'VOID');
    }

    public function isPosted(): bool
    {
        return $this->status === 'POSTED';
    }

    public function isVoid(): bool
    {
        return $this->status === 'VOID';
    }
}
