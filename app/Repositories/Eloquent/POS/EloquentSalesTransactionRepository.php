<?php

namespace App\Repositories\Eloquent\POS;

use App\Models\POS\SalesTransaction;
use App\Repositories\Contracts\POS\SalesTransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentSalesTransactionRepository implements SalesTransactionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return SalesTransaction::query()
            ->with(['cashier', 'customer', 'payments.paymentMethod'])
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where('transaction_no', 'like', "%{$search}%");
            })
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['cashier_id'] ?? null, fn($q, $cashierId) => $q->where('cashier_id', $cashierId))
            ->when($filters['customer_id'] ?? null, fn($q, $customerId) => $q->where('customer_id', $customerId))
            ->when($filters['date_from'] ?? null, fn($q, $date) => $q->where('transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($q, $date) => $q->where('transaction_date', '<=', $date))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?SalesTransaction
    {
        return SalesTransaction::with([
            'cashier',
            'customer',
            'items.product',
            'items.productVariant',
            'items.unit',
            'payments.paymentMethod',
            'discounts',
            'tax',
        ])->find($id);
    }

    public function findBySession(int $sessionId, int $perPage = 15): LengthAwarePaginator
    {
        return SalesTransaction::query()
            ->where('sales_session_id', $sessionId)
            ->with(['payments.paymentMethod', 'items'])
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): SalesTransaction
    {
        return SalesTransaction::create($data);
    }

    public function update(SalesTransaction $transaction, array $data): SalesTransaction
    {
        $transaction->update($data);
        return $transaction->fresh();
    }

    public function getDailySummary(\Carbon\Carbon $date): array
    {
        $query = SalesTransaction::where('transaction_date', $date->toDateString())
            ->where('status', 'POSTED');

        return [
            'total_sales'        => (clone $query)->sum('grand_total'),
            'total_discount'     => (clone $query)->sum('discount_amount'),
            'total_tax'          => (clone $query)->sum('tax_amount'),
            'transaction_count'  => (clone $query)->count(),
        ];
    }
}
