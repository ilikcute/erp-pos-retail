<?php

namespace App\Repositories\Contracts\MasterData;

use Illuminate\Database\Eloquent\Collection;

interface UnitConversionRepositoryInterface
{
    public function listAll(): Collection;

    public function listActive(): Collection;
}
