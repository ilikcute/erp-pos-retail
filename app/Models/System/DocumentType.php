<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $fillable = [
        'code',        // e.g. SALES_TRANSACTION
        'name',
        'prefix',      // e.g. POS
        'suffix',
        'date_format', // e.g. Ymd
        'padding',     // e.g. 4 → 0001
        'separator',   // e.g. -
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'padding' => 'integer',
    ];

    public function sequences()
    {
        return $this->hasMany(DocumentSequence::class);
    }
}
