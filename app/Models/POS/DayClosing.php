<?php

namespace App\Models\POS;

use App\Enums\POS\ClosingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DayClosing extends Model
{
    protected $fillable = [
        'closing_date',
        'closing_number',
        'location_id',
        'total_transactions',
        'total_sales',
        'total_cash',
        'total_non_cash',
        'total_discount',
        'total_tax',
        'total_opening_cash',
        'total_closing_cash',
        'total_expected_cash',
        'cash_difference',
        'status',
        'notes',
        'closed_by',
        'closed_at',
    ];

    protected $casts = [
        'closing_date' => 'date',
        'status' => ClosingStatus::class,
        'total_transactions' => 'integer',
        'total_sales' => 'decimal:2',
        'total_cash' => 'decimal:2',
        'total_non_cash' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_opening_cash' => 'decimal:2',
        'total_closing_cash' => 'decimal:2',
        'total_expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    // ═══════════════════════════════════════════════════════════
    // RELATIONS
    // ═══════════════════════════════════════════════════════════
    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Inventory\InventoryLocation::class, 'location_id');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'closed_by');
    }

    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(
            CashierSession::class,
            'day_closing_sessions',
            'day_closing_id',
            'cashier_session_id'
        )->withPivot(['session_sales', 'session_cash', 'session_transactions']);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SalesTransaction::class, 'day_closing_id');
    }

    // ═══════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════
    public function scopeOpen($query)
    {
        return $query->where('status', ClosingStatus::OPEN);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', ClosingStatus::CLOSED);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('closing_date', $date);
    }

    public function scopeForDateRange($query, $from, $to)
    {
        return $query->whereBetween('closing_date', [$from, $to]);
    }

    // ═══════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════
    public function isOpen(): bool
    {
        return $this->status === ClosingStatus::OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === ClosingStatus::CLOSED;
    }
}
