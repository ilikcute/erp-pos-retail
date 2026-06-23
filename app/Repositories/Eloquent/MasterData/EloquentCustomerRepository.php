<?php

namespace App\Repositories\Eloquent\MasterData;

use App\Models\MasterData\Customer;
use App\Repositories\Contracts\MasterData\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Customer::query()
            ->with('category')
            ->when(
                $filters['search'] ?? null,
                fn($q, $s) =>
                $q->where(
                    fn($q) => $q
                        ->where('customer_name', 'like', "%{$s}%")
                        ->orWhere('customer_code', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%")
                )
            )
            ->when($filters['customer_category_id'] ?? null, fn($q, $v) => $q->where('customer_category_id', $v))
            ->when($filters['city']                 ?? null, fn($q, $v) => $q->where('city', $v))
            ->when(isset($filters['is_active']),             fn($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Customer
    {
        return Customer::find($id);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);
        return $customer->fresh();
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
