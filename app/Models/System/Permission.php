<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',       // e.g. pos.transaction.create
        'module',     // e.g. pos
        'resource',   // e.g. transaction
        'action',     // e.g. create
        'display_name',
        'description',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withTimestamps();
    }

    /**
     * Parse permission string: {module}.{resource}.{action}
     */
    public static function fromString(string $permission): array
    {
        $parts = explode('.', $permission);
        return [
            'module'   => $parts[0] ?? null,
            'resource' => $parts[1] ?? null,
            'action'   => $parts[2] ?? null,
        ];
    }
}
