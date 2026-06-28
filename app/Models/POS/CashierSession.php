<?php

namespace App\Models\POS;

use App\Enums\POS\SessionStatus;
use App\Models\MasterData\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashierSession extends Model
{
    protected $fillable = [
        'session_no',
        'user_id',
        'shift_id',
        'location_id',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'total_sales',
        'total_transactions',
        'status',
        'notes',
        'opened_at',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'status' => SessionStatus::class,
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // ═══════════════════════════════════════════════════════════
    // RELATIONS
    // ═══════════════════════════════════════════════════════════
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class, 'shift_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Inventory\InventoryLocation::class, 'location_id');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ═══════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════
    public function scopeOpen($query)
    {
        return $query->where('status', SessionStatus::OPEN);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', SessionStatus::CLOSED);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ═══════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════
    public function isOpen(): bool
    {
        return $this->status === SessionStatus::OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === SessionStatus::CLOSED;
    }

    /**
     * Hitung expected cash dari semua transaksi cash di sesi ini
     */
    public function calculateExpectedCash(): float
    {
        return (float) $this->transactions()
            ->where('payment_method', 'cash')
            ->where('pay_later', false)
            ->sum('cash');
    }

    /**
     * Hitung total sales dari semua transaksi di sesi ini
     */
    public function calculateTotalSales(): float
    {
        return (float) $this->transactions()->sum('grand_total');
    }

    /**
     * Hitung total transaksi di sesi ini
     */
    public function calculateTotalTransactions(): int
    {
        return $this->transactions()->count();
    }
}
