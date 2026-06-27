<?php

namespace App\Repositories\Eloquent\Inventory;

use App\Models\Inventory\InventoryBalance;
use App\Repositories\Contracts\Inventory\BalanceRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BalanceRepository implements BalanceRepositoryInterface
{
    public function getBalances(array $filters = []): Collection
    {
        $query = InventoryBalance::with(['variant', 'location']);

        if (!empty($filters['product_variant_id'])) {
            $query->where('product_variant_id', $filters['product_variant_id']);
        }
        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }
        if (!empty($filters['low_stock'])) {
            $query->whereHas('variant', function ($q) {
                $q->whereColumn('inventory_balances.qty_available', '<', 'product_variants.reorder_point');
            });
        }

        return $query->get();
    }

    public function getBalance(int $variantId, int $locationId): ?object
    {
        return InventoryBalance::where('product_variant_id', $variantId)
            ->where('location_id', $locationId)
            ->first();
    }

    public function increment(int $variantId, int $locationId, float $qty): object
    {
        return DB::transaction(function () use ($variantId, $locationId, $qty) {
            $balance = InventoryBalance::firstOrCreate(
                ['product_variant_id' => $variantId, 'location_id' => $locationId],
                ['qty_on_hand' => 0, 'qty_reserved' => 0, 'qty_available' => 0]
            );

            $balance->increment('qty_on_hand', $qty);
            $balance->increment('qty_available', $qty);
            $balance->update(['last_movement_at' => now()]);

            return $balance->fresh();
        });
    }

    public function decrement(int $variantId, int $locationId, float $qty): object
    {
        return DB::transaction(function () use ($variantId, $locationId, $qty) {
            $balance = InventoryBalance::where('product_variant_id', $variantId)
                ->where('location_id', $locationId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($balance->qty_available < $qty) {
                throw new \DomainException("Stok tidak mencukupi. Tersedia: {$balance->qty_available}, diminta: {$qty}");
            }

            $balance->decrement('qty_on_hand', $qty);
            $balance->decrement('qty_available', $qty);
            $balance->update(['last_movement_at' => now()]);

            return $balance->fresh();
        });
    }

    public function reserve(int $variantId, int $locationId, float $qty): object
    {
        $balance = InventoryBalance::where('product_variant_id', $variantId)
            ->where('location_id', $locationId)
            ->lockForUpdate()
            ->firstOrFail();

        if ($balance->qty_available < $qty) {
            throw new \DomainException("Stok tersedia tidak cukup untuk di-reserve");
        }

        $balance->increment('qty_reserved', $qty);
        $balance->decrement('qty_available', $qty);

        return $balance->fresh();
    }

    public function releaseReservation(int $variantId, int $locationId, float $qty): object
    {
        $balance = InventoryBalance::where('product_variant_id', $variantId)
            ->where('location_id', $locationId)
            ->lockForUpdate()
            ->firstOrFail();

        $balance->decrement('qty_reserved', $qty);
        $balance->increment('qty_available', $qty);

        return $balance->fresh();
    }
}
