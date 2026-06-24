<?php

namespace App\Actions\Purchasing;

use App\Enums\DocumentStatus;
use App\Models\Purchasing\PurchaseOrder;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;

class CreatePurchaseOrderAction
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly AuditService $auditService,
    ) {}

    public function execute(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $poNo = $this->documentNumberService->generate('PURCHASE_ORDER');

            $purchaseOrder = PurchaseOrder::create([
                'purchase_order_no' => $poNo,
                'supplier_id'       => $data['supplier_id'],
                'po_date'           => $data['po_date'] ?? now()->toDateString(),
                'expected_date'     => $data['expected_date'],
                'status'            => DocumentStatus::DRAFT->value,
                'notes'             => $data['notes'] ?? null,
                'subtotal'          => 0,
                'tax_amount'        => 0,
                'grand_total'       => 0,
                'created_by'        => auth()->id(),
                'updated_by'        => auth()->id(),
            ]);

            $this->createPoItems($purchaseOrder, $data['items'] ?? []);
            $this->updatePoTotals($purchaseOrder);

            $this->auditService->log(
                module: 'Purchasing',
                action: 'CREATE_PURCHASE_ORDER',
                tableName: 'purchase_orders',
                recordId: $purchaseOrder->id,
                documentNo: $poNo,
                newValues: [
                    'supplier_id'   => $data['supplier_id'],
                    'expected_date' => $data['expected_date'],
                ],
            );

            return $purchaseOrder->fresh(['items']);
        });
    }

    private function createPoItems(PurchaseOrder $purchaseOrder, array $items): void
    {
        foreach ($items as $item) {
            $purchaseOrder->items()->create([
                'product_id'         => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'],
                'quantity'           => $item['quantity'],
                'unit_id'            => $item['unit_id'],
                'unit_cost'          => $item['unit_cost'],
                'tax_rate'           => $item['tax_rate'] ?? 0,
                'line_total'         => $item['quantity'] * $item['unit_cost'],
                'notes'              => $item['notes'] ?? null,
                'created_by'         => auth()->id(),
            ]);
        }
    }

    private function updatePoTotals(PurchaseOrder $purchaseOrder): void
    {
        $subtotal = $purchaseOrder->items()->sum('line_total');
        $taxAmount = $purchaseOrder->items()->sum(DB::raw('line_total * tax_rate / 100'));
        $grandTotal = $subtotal + $taxAmount;

        $purchaseOrder->update([
            'subtotal'    => $subtotal,
            'tax_amount'  => $taxAmount,
            'grand_total' => $grandTotal,
        ]);
    }
}
