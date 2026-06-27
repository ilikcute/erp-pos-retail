<?php

namespace App\Repositories\Eloquent\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Repositories\Contracts\Accounting\CoaRepositoryInterface;
use Illuminate\Support\Collection;

class CoaRepository implements CoaRepositoryInterface
{
    public function getAll(array $filters = []): Collection
    {
        $query = ChartOfAccount::with('parent')
            ->when(isset($filters['account_type']), fn($q) => $q->where('account_type', $filters['account_type']))
            ->when(isset($filters['is_active']), fn($q) => $q->where('is_active', $filters['is_active']))
            ->orderBy('account_code');

        return $query->get();
    }

    public function getTree(): Collection
    {
        $all = ChartOfAccount::with('children')
            ->whereNull('parent_id')
            ->orderBy('account_code')
            ->get();

        return $all;
    }

    public function findByCode(string $code): ?object
    {
        return ChartOfAccount::where('account_code', $code)->first();
    }

    public function findPostableAccounts(): Collection
    {
        return ChartOfAccount::where('is_postable', true)
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();
    }
}
