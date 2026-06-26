<?php

namespace App\Repositories\Contracts\MasterData;

use App\Models\MasterData\Currency;
use Illuminate\Database\Eloquent\Collection;

interface CurrencyRepositoryInterface
{
    public function listAll(): Collection;

    public function listActive(): Collection;

    public function findById(int $id): ?Currency;
}
