<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model
{
    protected $fillable = [
        'journal_number',
        'journal_date',
        'source_type',
        'source_id',
        'description',
        'status',
        'created_by',
        'posted_at',
    ];

    protected $casts = [
        'journal_date' => 'date',
        'posted_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function isBalanced(): bool
    {
        $totalDebit = $this->lines->sum('debit');
        $totalCredit = $this->lines->sum('credit');
        return abs($totalDebit - $totalCredit) < 0.01;
    }
}
