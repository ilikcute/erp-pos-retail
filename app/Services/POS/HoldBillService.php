<?php

namespace App\Services\POS;

use App\Models\POS\SalesHold;
use App\Repositories\Contracts\POS\SalesHoldRepositoryInterface;
use App\Repositories\Contracts\POS\SalesSessionRepositoryInterface;
use App\Support\AuditService;
use App\Support\DocumentNumberService;

class HoldBillService
{
    public function __construct(
        private readonly SalesHoldRepositoryInterface $holdRepository,
        private readonly SalesSessionRepositoryInterface $sessionRepository,
        private readonly DocumentNumberService $documentNumberService,
        private readonly AuditService $auditService,
    ) {}

    public function findById(int $id): ?SalesHold
    {
        return $this->holdRepository->findById($id);
    }

    public function findActiveBySession(int $sessionId)
    {
        return $this->holdRepository->findActiveBySession($sessionId);
    }

    public function findActiveByCashier(int $cashierId)
    {
        return $this->holdRepository->findActiveByCashier($cashierId);
    }

    public function create(array $data): SalesHold
    {
        $holdNo = $this->documentNumberService->generate('SALES_HOLD');

        $hold = $this->holdRepository->create([
            'hold_no' => $holdNo,
            'sales_session_id' => $data['sales_session_id'],
            'cashier_id' => $data['cashier_id'],
            'customer_id' => $data['customer_id'] ?? null,
            'status' => 'HELD',
            'subtotal' => $data['subtotal'] ?? 0,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'grand_total' => $data['grand_total'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
            'held_at' => now(),
        ]);

        // Create hold items
        if (! empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $hold->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'product_id' => $item['product_id'],
                    'item_name' => $item['item_name'],
                    'sku' => $item['sku'],
                    'barcode' => $item['barcode'] ?? null,
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'line_total' => $item['line_total'],
                    'created_by' => auth()->id(),
                ]);
            }
        }

        $this->auditService->log(
            module: 'POS',
            action: 'CREATE_HOLD_BILL',
            tableName: 'sales_holds',
            recordId: $hold->id,
            documentNo: $holdNo,
            newValues: ['grand_total' => $hold->grand_total],
        );

        return $hold->fresh(['items']);
    }

    public function resume(SalesHold $hold): SalesHold
    {
        if (! $hold->isHeld()) {
            throw new \RuntimeException('Hold bill is not active.');
        }

        $holdData = $hold->toArray();
        $itemsData = $hold->items->toArray();

        $this->holdRepository->resume($hold);

        $this->auditService->log(
            module: 'POS',
            action: 'RESUME_HOLD_BILL',
            tableName: 'sales_holds',
            recordId: $hold->id,
            documentNo: $hold->hold_no,
        );

        return [
            'hold' => $hold->fresh(),
            'items' => $itemsData,
        ];
    }

    public function cancel(SalesHold $hold): void
    {
        $this->holdRepository->cancel($hold);

        $this->auditService->log(
            module: 'POS',
            action: 'CANCEL_HOLD_BILL',
            tableName: 'sales_holds',
            recordId: $hold->id,
            documentNo: $hold->hold_no,
        );
    }
}
