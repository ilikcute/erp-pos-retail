<?php

namespace App\Services\Inventory;

use App\Enums\Inventory\TransactionType;
use App\Enums\Inventory\TransferStatus;
use App\Models\Inventory\InventoryTransfer;
use App\Repositories\Inventory\Contracts\BalanceRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferService
{
    public function __construct(
        private readonly StockMovementService $movement,
        private readonly BalanceRepositoryInterface $balanceRepo,
    ) {}

    public function create(array $data, int $userId): InventoryTransfer
    {
        // Validasi lokasi harus stock-bearing
        $source = \App\Models\Inventory\InventoryLocation::findOrFail($data['source_location_id']);
        $dest = \App\Models\Inventory\InventoryLocation::findOrFail($data['destination_location_id']);

        if (!$source->isStockBearing() || !$dest->isStockBearing()) {
            throw new \DomainException('Transfer hanya bisa antara lokasi stock-bearing');
        }

        return DB::transaction(function () use ($data, $userId) {
            $transfer = InventoryTransfer::create([
                'transfer_number' => 'TRF-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'source_location_id' => $data['source_location_id'],
                'destination_location_id' => $data['destination_location_id'],
                'transfer_date' => $data['transfer_date'],
                'remarks' => $data['remarks'] ?? null,
                'status' => TransferStatus::DRAFT,
                'created_by' => $userId,
            ]);

            foreach ($data['items'] as $item) {
                $batch = \App\Models\Inventory\InventoryBatch::findOrFail($item['inventory_batch_id']);
                $transfer->items()->create([
                    'inventory_batch_id' => $item['inventory_batch_id'],
                    'product_variant_id' => $batch->product_variant_id,
                    'transfer_qty' => $item['transfer_qty'],
                ]);
            }

            return $transfer;
        });
    }

    public function post(int $transferId, int $userId): InventoryTransfer
    {
        $transfer = InventoryTransfer::with('items.batch')->findOrFail($transferId);

        if ($transfer->status !== TransferStatus::DRAFT->value) {
            throw new \DomainException('Transfer sudah diposting atau dibatalkan');
        }

        return DB::transaction(function () use ($transfer, $userId) {
            foreach ($transfer->items as $item) {
                // Keluar dari source
                $this->movement->recordMovement(
                    TransactionType::TRANSFER_OUT,
                    $item->product_variant_id,
                    $transfer->source_location_id,
                    $item->transfer_qty,
                    reference: $transfer,
                    userId: $userId,
                );

                // Masuk ke destination
                $this->movement->recordMovement(
                    TransactionType::TRANSFER_IN,
                    $item->product_variant_id,
                    $transfer->destination_location_id,
                    $item->transfer_qty,
                    reference: $transfer,
                    userId: $userId,
                );
            }

            $transfer->update([
                'status' => TransferStatus::POSTED,
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            return $transfer;
        });
    }
}
