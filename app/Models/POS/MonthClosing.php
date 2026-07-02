<?php

namespace App\Models\POS;

use App\Enums\POS\ClosingStatus;
use App\Models\Inventory\InventoryLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthClosing extends Model
{
    protected $fillable = [
        'closing_year',
        'closing_month',
        'location_id',
        'total_days_closed',
        'total_transactions',
        'total_sales',
        'total_cash',
        'total_non_cash',
        'status',
        'notes',
        'closed_by',
        'closed_at',
    ];

    protected $casts = [
        'closing_year' => 'integer',
        'closing_month' => 'integer',
        'status' => ClosingStatus::class,
        'total_days_closed' => 'integer',
        'total_transactions' => 'integer',
        'total_sales' => 'decimal:2',
        'total_cash' => 'decimal:2',
        'total_non_cash' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    // ═══════════════════════════════════════════════════════════
    // RELATIONS
    // ═══════════════════════════════════════════════════════════
    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function dayClosings(): HasMany
    {
        return $this->hasMany(DayClosing::class)
            ->whereYear('closing_date', $this->closing_year)
            ->whereMonth('closing_date', $this->closing_month);
    }

    // ═══════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════
    public function scopeForPeriod($query, int $year, int $month)
    {
        return $query->where('closing_year', $year)
            ->where('closing_month', $month);
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
        return in_array($this->status, [ClosingStatus::CLOSED, ClosingStatus::LOCKED]);
    }

    public function isLocked(): bool
    {
        return $this->status === ClosingStatus::LOCKED;
    }

    public function getPeriodLabelAttribute(): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return ($months[$this->closing_month] ?? '').' '.$this->closing_year;
    }
}
