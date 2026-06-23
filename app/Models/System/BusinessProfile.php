<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class BusinessProfile extends Model
{
    protected $fillable = [
        'business_name',
        'legal_name',
        'tax_id',         // NPWP
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'website',
        'logo',
        'currency',       // IDR
        'timezone',       // Asia/Jakarta
        'updated_by',
    ];
}
