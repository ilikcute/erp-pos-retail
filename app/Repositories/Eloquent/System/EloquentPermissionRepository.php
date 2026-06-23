<?php

namespace App\Repositories\Eloquent\System;

use App\Models\System\Permission;
use App\Repositories\Contracts\System\PermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentPermissionRepository implements PermissionRepositoryInterface
{
    public function getAll(): Collection
    {
        return Permission::query()->orderBy('module')->orderBy('name')->get();
    }
}
