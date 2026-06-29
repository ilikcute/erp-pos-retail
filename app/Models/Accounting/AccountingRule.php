<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingRule extends Model
{
    protected $fillable = [
        'rule_name',
        'event_type',
        'journal_template_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(JournalTemplate::class, 'journal_template_id');
    }
}
