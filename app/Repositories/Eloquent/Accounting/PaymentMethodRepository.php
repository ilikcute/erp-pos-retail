<?php

namespace App\Repositories\Eloquent\Accounting;

use App\Models\Accounting\PaymentMethod;
use App\Repositories\Contracts\Accounting\PaymentMethodRepositoryInterface;
use Illuminate\Support\Collection;

class PaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    public function getAll(array $filters = []): Collection
    {
        return PaymentMethod::with('account')
            ->when(isset($filters['method_type']), fn ($q) => $q->where('method_type', $filters['method_type']))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->orderBy('sort_order')
            ->get();
    }

    public function findActive(): Collection
    {
        return PaymentMethod::with('account')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function findByCode(string $code): ?object
    {
        return PaymentMethod::with('account')
            ->where('method_code', $code)
            ->first();
    }

    public function findByType(string $type): ?object
    {
        return PaymentMethod::with('account')
            ->where('method_type', $type)
            ->where('is_active', true)
            ->first();
    }
}
