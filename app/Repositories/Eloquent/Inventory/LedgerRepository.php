<?php

namespace App\Repositories\Eloquent\Inventory;

use App\Models\Inventory\InventoryLedger;
use App\Repositories\Contracts\Inventory\LedgerRepositoryInterface;
use Illuminate\Support\Collection;

class LedgerRepository implements LedgerRepositoryInterface
{
    public function create(array $data): object
    {
        return InventoryLedger::create($data);
    }

    public function getLedgers(array $filters = []): Collection
    {
        $query = InventoryLedger::with(['variant', 'location', 'batch']);

        if (! empty($filters['product_variant_id'])) {
            $query->where('product_variant_id', $filters['product_variant_id']);
        }
        if (! empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }
        if (! empty($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('transaction_date', 'desc')->get();
    }

    public function generateReferenceNumber(string $prefix): string
    {
        $date = now()->format('Ymd');
        $last = InventoryLedger::whereDate('created_at', today())
            ->where('reference_number', 'like', "{$prefix}-{$date}-%")
            ->count();

        return sprintf('%s-%s-%04d', $prefix, $date, $last + 1);
    }
}
