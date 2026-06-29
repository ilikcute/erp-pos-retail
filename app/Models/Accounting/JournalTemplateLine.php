<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalTemplateLine extends Model
{
    protected $fillable = [
        'journal_template_id',
        'account_id',
        'direction',
        'formula',
        'description',
        'sort_order',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(JournalTemplate::class, 'journal_template_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
