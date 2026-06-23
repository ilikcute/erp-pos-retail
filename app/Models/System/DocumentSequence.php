<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class DocumentSequence extends Model
{
    protected $fillable = [
        'document_type_id',
        'period',          // e.g. 20260621
        'last_sequence',
    ];

    protected $casts = [
        'last_sequence' => 'integer',
    ];

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }
}
