<?php

namespace App\Actions\Inventory;

use App\Enums\DocumentStatus;
use App\Models\Inventory\StockTransfer;
use App\Repositories\Contracts\Inventory\InventoryLedgerRepositoryInterface;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;

class PostStockTransferAction
{
    public function __construct(
        private readonly InventoryLedgerRepositoryInterface $inventoryLedgerRepository,
        private readonly DocumentNumberService $documentNumberService,
        private readonly AuditService $auditService,
    ) {}

    public function execute(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data) {
            $transferNo = $this->documentNumberService->generate('STOCK_TRANSFER');

            $transfer = StockTransfer::create([
                'transfer_no' => $transferNo,
                'from_location_id' => $data['from_location_id'],
                'to_location_id' => $data['to_location_id'],
                'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                'status' => DocumentStatus::POSTED->value,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            $this->createTransferItems($transfer, $data['items'] ?? []);
            $this->postInventoryLedger($transfer);

            $this->auditService->log(
                module: 'Inventory',
                action: 'POST_STOCK_TRANSFER',
                tableName: 'stock_transfers',
                recordId: $transfer->id,
                documentNo: $transferNo,
                newValues: [
                    'from_location_id' => $data['from_location_id'],
                    'to_location_id' => $data['to_location_id'],
                ],
            );

            return $transfer->fresh(['items']);
        });
    }

    private function createTransferItems(StockTransfer $transfer, array $items): void
    {
        foreach ($items as $item) {
            $transfer->items()->create([
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'],
                'quantity' => $item['quantity'],
                'unit_id' => $item['unit_id'],
                'unit_cost' => $item['unit_cost'] ?? 0,
                'notes' => $item['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);
        }
    }

    private function postInventoryLedger(StockTransfer $transfer): void
    {
        foreach ($transfer->items as $item) {
            $this->inventoryLedgerRepository->create([
                'document_type' => 'STOCK_TRANSFER_OUT',
                'document_id' => $transfer->id,
                'document_no' => $transfer->transfer_no,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'location_id' => $transfer->from_location_id,
                'movement_type' => 'OUT',
                'quantity' => -$item->quantity,
                'unit_cost' => $item->unit_cost,
                'reference_date' => $transfer->transfer_date,
                'notes' => "Transfer OUT: {$transfer->transfer_no}",
                'created_by' => auth()->id(),
            ]);

            $this->inventoryLedgerRepository->create([
                'document_type' => 'STOCK_TRANSFER_IN',
                'document_id' => $transfer->id,
                'document_no' => $transfer->transfer_no,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'location_id' => $transfer->to_location_id,
                'movement_type' => 'IN',
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_cost,
                'reference_date' => $transfer->transfer_date,
                'notes' => "Transfer IN: {$transfer->transfer_no}",
                'created_by' => auth()->id(),
            ]);
        }
    }
}
