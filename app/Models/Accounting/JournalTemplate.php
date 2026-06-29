<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalTemplate extends Model
{
    protected $fillable = [
        'template_code',
        'template_name',
        'event_type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalTemplateLine::class)->orderBy('sort_order');
    }
}
