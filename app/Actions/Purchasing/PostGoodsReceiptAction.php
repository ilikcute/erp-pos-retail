<?php

namespace App\Actions\Purchasing;

use App\Enums\DocumentStatus;
use App\Models\Purchasing\GoodsReceipt;
use App\Repositories\Contracts\Inventory\InventoryLedgerRepositoryInterface;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;

class PostGoodsReceiptAction
{
    public function __construct(
        private readonly InventoryLedgerRepositoryInterface $inventoryLedgerRepository,
        private readonly DocumentNumberService $documentNumberService,
        private readonly AuditService $auditService,
    ) {}

    public function execute(array $data): GoodsReceipt
    {
        return DB::transaction(function () use ($data) {
            $grNo = $this->documentNumberService->generate('GOODS_RECEIPT');

            $goodsReceipt = GoodsReceipt::create([
                'goods_receipt_no' => $grNo,
                'purchase_order_id' => $data['purchase_order_id'],
                'supplier_id' => $data['supplier_id'],
                'location_id' => $data['location_id'],
                'receipt_date' => $data['receipt_date'] ?? now()->toDateString(),
                'status' => DocumentStatus::POSTED->value,
                'notes' => $data['notes'] ?? null,
                'total_qty' => 0,
                'total_amount' => 0,
                'created_by' => auth()->id(),
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            $this->createReceiptItems($goodsReceipt, $data['items'] ?? []);
            $this->postInventoryLedger($goodsReceipt);
            $this->updateReceiptTotals($goodsReceipt);

            $this->auditService->log(
                module: 'Purchasing',
                action: 'POST_GOODS_RECEIPT',
                tableName: 'goods_receipts',
                recordId: $goodsReceipt->id,
                documentNo: $grNo,
                newValues: [
                    'purchase_order_id' => $data['purchase_order_id'],
                    'supplier_id' => $data['supplier_id'],
                ],
            );

            return $goodsReceipt->fresh(['items']);
        });
    }

    private function createReceiptItems(GoodsReceipt $goodsReceipt, array $items): void
    {
        foreach ($items as $item) {
            $goodsReceipt->items()->create([
                'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'],
                'quantity_received' => $item['quantity_received'],
                'quantity_ordered' => $item['quantity_ordered'] ?? $item['quantity_received'],
                'unit_id' => $item['unit_id'],
                'unit_cost' => $item['unit_cost'],
                'line_total' => $item['quantity_received'] * $item['unit_cost'],
                'notes' => $item['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);
        }
    }

    private function postInventoryLedger(GoodsReceipt $goodsReceipt): void
    {
        foreach ($goodsReceipt->items as $item) {
            $this->inventoryLedgerRepository->create([
                'document_type' => 'GOODS_RECEIPT',
                'document_id' => $goodsReceipt->id,
                'document_no' => $goodsReceipt->goods_receipt_no,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'location_id' => $goodsReceipt->location_id,
                'movement_type' => 'IN',
                'quantity' => $item->quantity_received,
                'unit_cost' => $item->unit_cost,
                'reference_date' => $goodsReceipt->receipt_date,
                'notes' => "GR: {$goodsReceipt->goods_receipt_no} - {$item->quantity_received} units",
                'created_by' => auth()->id(),
            ]);
        }
    }

    private function updateReceiptTotals(GoodsReceipt $goodsReceipt): void
    {
        $totalQty = $goodsReceipt->items()->sum('quantity_received');
        $totalAmount = $goodsReceipt->items()->sum('line_total');

        $goodsReceipt->update([
            'total_qty' => $totalQty,
            'total_amount' => $totalAmount,
        ]);
    }
}
