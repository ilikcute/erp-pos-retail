<?php

namespace App\Actions\Reporting;

use App\Models\POS\SalesTransaction;
use App\Models\Inventory\InventoryBalance;
use App\Models\Accounting\GeneralLedger;
use Illuminate\Support\Facades\DB;

class GenerateSalesReportAction
{
    public function execute(array $filters): array
    {
        $query = SalesTransaction::query()
            ->whereBetween('transaction_date', [
                $filters['date_from'] ?? now()->startOfMonth()->toDateString(),
                $filters['date_to'] ?? now()->toDateString(),
            ])
            ->where('status', 'POSTED');

        if (isset($filters['cashier_id'])) {
            $query->where('cashier_id', $filters['cashier_id']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        $transactions = $query->with(['items', 'payments', 'cashier', 'customer'])
            ->get();

        return [
            'total_sales'        => $transactions->sum('grand_total'),
            'total_transactions' => $transactions->count(),
            'total_items_sold'   => $transactions->sum(function ($tx) {
                return $tx->items->sum('quantity');
            }),
            'average_sale'       => $transactions->count() > 0
                ? $transactions->sum('grand_total') / $transactions->count()
                : 0,
            'payment_summary'    => $this->getPaymentSummary($transactions),
            'transactions'       => $transactions,
        ];
    }

    private function getPaymentSummary($transactions): array
    {
        $paymentSummary = [];

        foreach ($transactions as $transaction) {
            foreach ($transaction->payments as $payment) {
                $methodName = $payment->paymentMethod->name ?? 'Unknown';
                if (!isset($paymentSummary[$methodName])) {
                    $paymentSummary[$methodName] = 0;
                }
                $paymentSummary[$methodName] += $payment->amount;
            }
        }

        return $paymentSummary;
    }
}
