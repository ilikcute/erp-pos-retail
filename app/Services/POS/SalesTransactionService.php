<?php

namespace App\Services\POS;

use App\Models\POS\SalesTransaction;
use App\Repositories\Contracts\POS\SalesSessionRepositoryInterface;
use App\Repositories\Contracts\POS\SalesTransactionRepositoryInterface;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SalesTransactionService
{
    public function __construct(
        private readonly SalesTransactionRepositoryInterface $transactionRepository,
        private readonly SalesSessionRepositoryInterface $sessionRepository,
        private readonly DocumentNumberService $documentNumberService,
        private readonly AuditService $auditService,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->transactionRepository->paginate($filters, $perPage);
    }

    public function findById(int $id): ?SalesTransaction
    {
        return $this->transactionRepository->findById($id);
    }

    public function findBySession(int $sessionId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->transactionRepository->findBySession($sessionId, $perPage);
    }

    public function create(array $data): SalesTransaction
    {
        $session = $this->sessionRepository->findById($data['sales_session_id']);

        if (! $session || ! $session->isOpen()) {
            throw new \RuntimeException('Sales session is not open.');
        }

        $transactionNo = $this->documentNumberService->generate('SALES_TRANSACTION');

        $transaction = $this->transactionRepository->create([
            'transaction_no' => $transactionNo,
            'sales_session_id' => $data['sales_session_id'],
            'cashier_id' => $data['cashier_id'],
            'customer_id' => $data['customer_id'] ?? null,
            'transaction_date' => now()->toDateString(),
            'status' => 'POSTED',
            'subtotal' => $data['subtotal'],
            'discount_amount' => $data['discount_amount'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'grand_total' => $data['grand_total'],
            'paid_amount' => $data['paid_amount'] ?? 0,
            'change_amount' => $data['change_amount'] ?? 0,
            'tax_id' => $data['tax_id'] ?? null,
            'tax_rate' => $data['tax_rate'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);

        // Create transaction items
        if (! empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $transaction->items()->create([
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
                    'cost_price' => $item['cost_price'] ?? 0,
                    'created_by' => auth()->id(),
                ]);
            }
        }

        // Create payments
        if (! empty($data['payments'])) {
            foreach ($data['payments'] as $payment) {
                $paymentNo = $this->documentNumberService->generate('SALES_PAYMENT');

                $transaction->payments()->create([
                    'payment_no' => $paymentNo,
                    'payment_method_id' => $payment['payment_method_id'],
                    'amount' => $payment['amount'],
                    'reference_no' => $payment['reference_no'] ?? null,
                    'status' => 'POSTED',
                    'notes' => $payment['notes'] ?? null,
                    'created_by' => auth()->id(),
                    'posted_by' => auth()->id(),
                    'posted_at' => now(),
                ]);
            }
        }

        // Create discounts
        if (! empty($data['discounts'])) {
            foreach ($data['discounts'] as $discount) {
                $transaction->discounts()->create([
                    'sales_transaction_item_id' => $discount['sales_transaction_item_id'] ?? null,
                    'discount_type' => $discount['discount_type'],
                    'discount_value' => $discount['discount_value'] ?? 0,
                    'discount_amount' => $discount['discount_amount'],
                    'promotion_id' => $discount['promotion_id'] ?? null,
                    'description' => $discount['description'] ?? null,
                    'created_by' => auth()->id(),
                ]);
            }
        }

        // Update session totals
        $this->updateSessionTotals($session);

        $this->auditService->log(
            module: 'POS',
            action: 'CREATE_TRANSACTION',
            tableName: 'sales_transactions',
            recordId: $transaction->id,
            documentNo: $transactionNo,
            newValues: [
                'grand_total' => $transaction->grand_total,
                'paid_amount' => $transaction->paid_amount,
                'customer_id' => $transaction->customer_id,
            ],
        );

        return $transaction->fresh(['items', 'payments', 'discounts']);
    }

    public function voidTransaction(SalesTransaction $transaction, string $reason): SalesTransaction
    {
        if (! $transaction->isPosted()) {
            throw new \RuntimeException('Only posted transactions can be voided.');
        }

        $transaction->update([
            'status' => 'VOID',
            'voided_by' => auth()->id(),
            'voided_at' => now(),
            'void_reason' => $reason,
        ]);

        $session = $this->sessionRepository->findById($transaction->sales_session_id);
        if ($session) {
            $this->updateSessionTotals($session);
        }

        $this->auditService->log(
            module: 'POS',
            action: 'VOID_TRANSACTION',
            tableName: 'sales_transactions',
            recordId: $transaction->id,
            documentNo: $transaction->transaction_no,
            statusBefore: 'POSTED',
            statusAfter: 'VOID',
            reason: $reason,
        );

        return $transaction->fresh();
    }

    private function updateSessionTotals($session): void
    {
        $postedTransactions = $session->transactions()->where('status', 'POSTED');

        $totalSales = $postedTransactions->sum('grand_total');
        $transactionCount = $postedTransactions->count();

        $this->sessionRepository->update($session, [
            'total_sales' => $totalSales,
            'total_transactions' => $totalSales,
            'transaction_count' => $transactionCount,
        ]);
    }
}
