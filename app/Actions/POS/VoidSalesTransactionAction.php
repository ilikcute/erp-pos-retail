<?php

namespace App\Actions\POS;

use App\Enums\DocumentStatus;
use App\Models\POS\SalesTransaction;
use App\Models\POS\SalesVoid;
use App\Repositories\Contracts\POS\SalesTransactionRepositoryInterface;
use App\Repositories\Contracts\Inventory\InventoryLedgerRepositoryInterface;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;

class VoidSalesTransactionAction
{
    public function __construct(
        private readonly SalesTransactionRepositoryInterface $transactionRepository,
        private readonly InventoryLedgerRepositoryInterface $inventoryLedgerRepository,
        private readonly DocumentNumberService $documentNumberService,
        private readonly AuditService $auditService,
    ) {}

    public function execute(SalesTransaction $transaction, string $reason): SalesTransaction
    {
        return DB::transaction(function () use ($transaction, $reason) {
            if ($transaction->status !== DocumentStatus::POSTED->value) {
                throw new \RuntimeException('Only posted transactions can be voided.');
            }

            $voidNo = $this->documentNumberService->generate('SALES_VOID');

            SalesVoid::create([
                'void_no'              => $voidNo,
                'sales_transaction_id' => $transaction->id,
                'transaction_no'       => $transaction->transaction_no,
                'void_reason'          => $reason,
                'void_date'            => now()->toDateString(),
                'void_amount'          => $transaction->grand_total,
                'status'               => DocumentStatus::POSTED->value,
                'created_by'           => auth()->id(),
                'voided_by'            => auth()->id(),
                'voided_at'            => now(),
            ]);

            $transaction->update([
                'status'    => DocumentStatus::VOID->value,
                'voided_by' => auth()->id(),
                'voided_at' => now(),
            ]);

            $this->reverseInventoryLedger($transaction);

            $this->auditService->log(
                module: 'POS',
                action: 'VOID_SALES_TRANSACTION',
                tableName: 'sales_transactions',
                recordId: $transaction->id,
                documentNo: $transaction->transaction_no,
                statusBefore: DocumentStatus::POSTED->value,
                statusAfter: DocumentStatus::VOID->value,
                reason: $reason,
            );

            return $transaction->fresh();
        });
    }

    private function reverseInventoryLedger(SalesTransaction $transaction): void
    {
        foreach ($transaction->items as $item) {
            $this->inventoryLedgerRepository->create([
                'document_type'      => 'SALES_VOID',
                'document_id'        => $transaction->id,
                'document_no'        => $transaction->transaction_no,
                'product_id'         => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'location_id'        => null,
                'movement_type'      => 'IN',
                'quantity'           => $item->quantity,
                'unit_cost'          => $item->cost_price,
                'reference_date'     => now()->toDateString(),
                'notes'              => "POS Void: {$transaction->transaction_no} - {$item->quantity} units",
                'created_by'         => auth()->id(),
            ]);
        }
    }
}
