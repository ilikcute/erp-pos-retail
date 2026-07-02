<?php

namespace App\Models\POS;

use App\Models\System\User;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesSession extends Model
{
    use HasCreatedBy;

    protected $fillable = [
        'session_no',
        'shift_id',
        'cashier_id',
        'session_date',
        'status',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'total_sales',
        'total_transactions',
        'transaction_count',
        'notes',
        'created_by',
        'updated_by',
        'closed_at',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_transactions' => 'decimal:2',
        'session_date' => 'date',
        'closed_at' => 'datetime',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SalesTransaction::class);
    }

    public function holds(): HasMany
    {
        return $this->hasMany(SalesHold::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'OPEN');
    }

    public function isOpen(): bool
    {
        return $this->status === 'OPEN';
    }
}
