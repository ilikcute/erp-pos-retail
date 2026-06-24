<?php

namespace App\Actions\Inventory;

use App\Enums\DocumentStatus;
use App\Models\Inventory\StockAdjustment;
use App\Repositories\Contracts\Inventory\InventoryLedgerRepositoryInterface;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;

class PostStockAdjustmentAction
{
    public function __construct(
        private readonly InventoryLedgerRepositoryInterface $inventoryLedgerRepository,
        private readonly DocumentNumberService $documentNumberService,
        private readonly AuditService $auditService,
    ) {}

    public function execute(array $data): StockAdjustment
    {
        return DB::transaction(function () use ($data) {
            $adjustmentNo = $this->documentNumberService->generate('STOCK_ADJUSTMENT');

            $adjustment = StockAdjustment::create([
                'adjustment_no'   => $adjustmentNo,
                'location_id'     => $data['location_id'],
                'adjustment_date' => $data['adjustment_date'] ?? now()->toDateString(),
                'adjustment_type' => $data['adjustment_type'],
                'reason'          => $data['reason'],
                'status'          => DocumentStatus::POSTED->value,
                'notes'           => $data['notes'] ?? null,
                'created_by'      => auth()->id(),
                'posted_by'       => auth()->id(),
                'posted_at'       => now(),
            ]);

            $this->createAdjustmentItems($adjustment, $data['items'] ?? []);
            $this->postInventoryLedger($adjustment);

            $this->auditService->log(
                module: 'Inventory',
                action: 'POST_STOCK_ADJUSTMENT',
                tableName: 'stock_adjustments',
                recordId: $adjustment->id,
                documentNo: $adjustmentNo,
                newValues: [
                    'adjustment_type' => $data['adjustment_type'],
                    'reason'          => $data['reason'],
                ],
            );

            return $adjustment->fresh(['items']);
        });
    }

    private function createAdjustmentItems(StockAdjustment $adjustment, array $items): void
    {
        foreach ($items as $item) {
            $adjustment->items()->create([
                'product_id'         => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'],
                'quantity_variance'  => $item['quantity_variance'],
                'unit_id'            => $item['unit_id'],
                'unit_cost'          => $item['unit_cost'] ?? 0,
                'notes'              => $item['notes'] ?? null,
                'created_by'         => auth()->id(),
            ]);
        }
    }

    private function postInventoryLedger(StockAdjustment $adjustment): void
    {
        $movementType = $adjustment->adjustment_type === 'INCREASE' ? 'IN' : 'OUT';
        $quantity = $adjustment->adjustment_type === 'INCREASE'
            ? 1
            : -1;

        foreach ($adjustment->items as $item) {
            $this->inventoryLedgerRepository->create([
                'document_type'      => 'STOCK_ADJUSTMENT',
                'document_id'        => $adjustment->id,
                'document_no'        => $adjustment->adjustment_no,
                'product_id'         => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'location_id'        => $adjustment->location_id,
                'movement_type'      => $movementType,
                'quantity'           => $item->quantity_variance * $quantity,
                'unit_cost'          => $item->unit_cost,
                'reference_date'     => $adjustment->adjustment_date,
                'notes'              => "Adjustment ({$adjustment->adjustment_type}): {$adjustment->adjustment_no} - {$adjustment->reason}",
                'created_by'         => auth()->id(),
            ]);
        }
    }
}
