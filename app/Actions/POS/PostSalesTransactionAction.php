<?php

namespace App\Actions\POS;

use App\Enums\DocumentStatus;
use App\Models\POS\SalesTransaction;
use App\Models\POS\SalesSession;
use App\Repositories\Contracts\POS\SalesTransactionRepositoryInterface;
use App\Repositories\Contracts\Inventory\InventoryLedgerRepositoryInterface;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;

class PostSalesTransactionAction
{
    public function __construct(
        private readonly SalesTransactionRepositoryInterface $transactionRepository,
        private readonly InventoryLedgerRepositoryInterface $inventoryLedgerRepository,
        private readonly DocumentNumberService $documentNumberService,
        private readonly AuditService $auditService,
    ) {}

    public function execute(array $data): SalesTransaction
    {
        return DB::transaction(function () use ($data) {
            $transactionNo = $this->documentNumberService->generate('SALES_TRANSACTION');

            $transaction = $this->transactionRepository->create([
                'transaction_no'   => $transactionNo,
                'sales_session_id' => $data['sales_session_id'],
                'cashier_id'       => $data['cashier_id'],
                'customer_id'      => $data['customer_id'] ?? null,
                'transaction_date' => now()->toDateString(),
                'status'           => DocumentStatus::POSTED->value,
                'subtotal'         => $data['subtotal'],
                'discount_amount'  => $data['discount_amount'] ?? 0,
                'tax_amount'       => $data['tax_amount'] ?? 0,
                'grand_total'      => $data['grand_total'],
                'paid_amount'      => $data['paid_amount'] ?? 0,
                'change_amount'    => $data['change_amount'] ?? 0,
                'tax_id'           => $data['tax_id'] ?? null,
                'tax_rate'         => $data['tax_rate'] ?? 0,
                'notes'            => $data['notes'] ?? null,
                'created_by'       => auth()->id(),
                'updated_by'       => auth()->id(),
                'posted_by'        => auth()->id(),
                'posted_at'        => now(),
            ]);

            $this->createTransactionItems($transaction, $data['items'] ?? []);
            $this->createPayments($transaction, $data['payments'] ?? []);
            $this->createDiscounts($transaction, $data['discounts'] ?? []);
            $this->postInventoryLedger($transaction);

            $this->auditService->log(
                module: 'POS',
                action: 'POST_SALES_TRANSACTION',
                tableName: 'sales_transactions',
                recordId: $transaction->id,
                documentNo: $transactionNo,
                newValues: [
                    'grand_total'  => $transaction->grand_total,
                    'paid_amount'  => $transaction->paid_amount,
                    'customer_id'  => $transaction->customer_id,
                ],
            );

            return $transaction->fresh(['items', 'payments', 'discounts']);
        });
    }

    private function createTransactionItems(SalesTransaction $transaction, array $items): void
    {
        foreach ($items as $item) {
            $transaction->items()->create([
                'product_variant_id' => $item['product_variant_id'],
                'product_id'         => $item['product_id'],
                'item_name'          => $item['item_name'],
                'sku'                => $item['sku'],
                'barcode'            => $item['barcode'] ?? null,
                'unit_id'            => $item['unit_id'],
                'quantity'           => $item['quantity'],
                'unit_price'         => $item['unit_price'],
                'discount_amount'    => $item['discount_amount'] ?? 0,
                'tax_amount'         => $item['tax_amount'] ?? 0,
                'line_total'         => $item['line_total'],
                'cost_price'         => $item['cost_price'] ?? 0,
                'created_by'         => auth()->id(),
            ]);
        }
    }

    private function createPayments(SalesTransaction $transaction, array $payments): void
    {
        foreach ($payments as $payment) {
            $paymentNo = $this->documentNumberService->generate('SALES_PAYMENT');

            $transaction->payments()->create([
                'payment_no'        => $paymentNo,
                'payment_method_id' => $payment['payment_method_id'],
                'amount'            => $payment['amount'],
                'reference_no'      => $payment['reference_no'] ?? null,
                'status'            => DocumentStatus::POSTED->value,
                'notes'             => $payment['notes'] ?? null,
                'created_by'        => auth()->id(),
                'posted_by'         => auth()->id(),
                'posted_at'         => now(),
            ]);
        }
    }

    private function createDiscounts(SalesTransaction $transaction, array $discounts): void
    {
        foreach ($discounts as $discount) {
            $transaction->discounts()->create([
                'sales_transaction_item_id' => $discount['sales_transaction_item_id'] ?? null,
                'discount_type'             => $discount['discount_type'],
                'discount_value'            => $discount['discount_value'] ?? 0,
                'discount_amount'           => $discount['discount_amount'],
                'promotion_id'              => $discount['promotion_id'] ?? null,
                'description'               => $discount['description'] ?? null,
                'created_by'                => auth()->id(),
            ]);
        }
    }

    private function postInventoryLedger(SalesTransaction $transaction): void
    {
        foreach ($transaction->items as $item) {
            $this->inventoryLedgerRepository->create([
                'document_type'      => 'SALES_TRANSACTION',
                'document_id'        => $transaction->id,
                'document_no'        => $transaction->transaction_no,
                'product_id'         => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'location_id'        => null,
                'movement_type'      => 'OUT',
                'quantity'           => -$item->quantity,
                'unit_cost'          => $item->cost_price,
                'reference_date'     => $transaction->transaction_date,
                'notes'              => "POS Sale: {$transaction->transaction_no}",
                'created_by'         => auth()->id(),
            ]);
        }
    }
}
