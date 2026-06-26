<?php

namespace App\Repositories\Eloquent\MasterData;

use App\Models\MasterData\Currency;
use App\Repositories\Contracts\MasterData\CurrencyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentCurrencyRepository implements CurrencyRepositoryInterface
{
    public function listAll(): Collection
    {
        return Currency::query()->orderBy('name')->get();
    }

    public function listActive(): Collection
    {
        return Currency::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function findById(int $id): ?Currency
    {
        return Currency::find($id);
    }
}
