<?php

namespace App\Repositories\Eloquent\MasterData;

use App\Models\MasterData\UnitConversion;
use App\Repositories\Contracts\MasterData\UnitConversionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentUnitConversionRepository implements UnitConversionRepositoryInterface
{
    public function listAll(): Collection
    {
        return UnitConversion::with(['fromUnit', 'toUnit'])->get();
    }

    public function listActive(): Collection
    {
        return UnitConversion::with(['fromUnit', 'toUnit'])->where('is_active', true)->get();
    }
}
