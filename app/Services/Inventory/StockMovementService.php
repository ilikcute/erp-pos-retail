<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\TransactionType;
use App\Models\Inventory\InventoryBalance;
use App\Repositories\Contracts\Inventory\BalanceRepositoryInterface;
use App\Repositories\Contracts\Inventory\LedgerRepositoryInterface;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public function __construct(
        private readonly BalanceRepositoryInterface $balanceRepo,
        private readonly LedgerRepositoryInterface $ledgerRepo,
    ) {}

    /**
     * Entry point utama untuk semua gerakan stok.
     * Transaction-safe, konsisten, dan bisa di-audit.
     */
    public function recordMovement(
        TransactionType $type,
        int $variantId,
        int $locationId,
        float $qty,
        float $unitCost = 0,
        ?int $batchId = null,
        $reference = null,
        ?string $notes = null,
        ?int $userId = null,
    ): object {
        return DB::transaction(function () use ($type, $variantId, $locationId, $qty, $unitCost, $batchId, $reference, $notes, $userId) {
            // 1. Ambil balance saat ini
            $balance = InventoryBalance::firstOrCreate(
                ['product_variant_id' => $variantId, 'location_id' => $locationId],
                ['qty_on_hand' => 0, 'qty_reserved' => 0, 'qty_available' => 0]
            );

            $qtyBefore = $balance->qty_on_hand;
            $isIncrease = $type->isIncrease();

            // 2. Update balance
            if ($isIncrease) {
                $this->balanceRepo->increment($variantId, $locationId, abs($qty));
                $qtyChange = abs($qty);
            } else {
                $this->balanceRepo->decrement($variantId, $locationId, abs($qty));
                $qtyChange = -abs($qty);
            }

            $qtyAfter = $qtyBefore + $qtyChange;

            // 3. Catat ke ledger
            return $this->ledgerRepo->create([
                'reference_number' => $this->ledgerRepo->generateReferenceNumber($type->value),
                'transaction_type' => $type,
                'product_variant_id' => $variantId,
                'location_id' => $locationId,
                'inventory_batch_id' => $batchId,
                'qty_change' => $qtyChange,
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'unit_cost' => $unitCost,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'user_id' => $userId,
                'notes' => $notes,
                'transaction_date' => now(),
            ]);
        });
    }
}
