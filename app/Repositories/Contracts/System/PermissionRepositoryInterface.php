<?php

namespace App\Repositories\Contracts\System;

use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface
{
    public function getAll(): Collection;
}
