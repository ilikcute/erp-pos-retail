<?php

namespace App\Repositories\Eloquent\POS;

use App\Models\POS\SalesHold;
use App\Repositories\Contracts\POS\SalesHoldRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentSalesHoldRepository implements SalesHoldRepositoryInterface
{
    public function findById(int $id): ?SalesHold
    {
        return SalesHold::with(['items.product', 'items.productVariant', 'items.unit', 'customer'])->find($id);
    }

    public function findActiveBySession(int $sessionId): Collection
    {
        return SalesHold::where('sales_session_id', $sessionId)
            ->where('status', 'HELD')
            ->with(['items.product', 'items.unit'])
            ->latest()
            ->get();
    }

    public function findActiveByCashier(int $cashierId): Collection
    {
        return SalesHold::where('cashier_id', $cashierId)
            ->where('status', 'HELD')
            ->with(['items.product', 'items.unit'])
            ->latest()
            ->get();
    }

    public function create(array $data): SalesHold
    {
        return SalesHold::create($data);
    }

    public function update(SalesHold $hold, array $data): SalesHold
    {
        $hold->update($data);
        return $hold->fresh();
    }

    public function resume(SalesHold $hold): void
    {
        $hold->update([
            'status'     => 'RESUMED',
            'resumed_at' => now(),
        ]);
    }

    public function cancel(SalesHold $hold): void
    {
        $hold->update(['status' => 'CANCELLED']);
    }
}
