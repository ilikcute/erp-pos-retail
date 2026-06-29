<?php

namespace App\Actions\POS;

use App\Enums\POS\SessionStatus;
use App\Models\POS\CashierSession;
use App\Support\AuditService;
use Illuminate\Support\Facades\DB;

class CloseSalesSessionAction
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function execute(CashierSession $session, array $data): CashierSession
    {
        return DB::transaction(function () use ($session, $data) {
            if ($session->isClosed()) {
                throw new \DomainException('Sesi sudah ditutup.');
            }

            // Hitung dari transaksi aktual
            $expectedCash      = $session->calculateExpectedCash();
            $totalSales        = $session->calculateTotalSales();
            $totalTransactions = $session->calculateTotalTransactions();
            $closingCash       = $data['closing_cash'] ?? 0;
            $cashDifference    = $closingCash - ($session->opening_cash + $expectedCash);

            $session->update([
                'closing_cash'       => $closingCash,
                'expected_cash'      => $expectedCash,
                'total_sales'        => $totalSales,
                'total_transactions' => $totalTransactions,
                'cash_difference'    => $cashDifference,
                'status'             => SessionStatus::CLOSED,
                'closed_at'          => now(),
                'closed_by'          => auth()->id(),
                'notes'              => isset($data['notes'])
                    ? trim($session->notes . "\n" . $data['notes'])
                    : $session->notes,
            ]);

            $this->auditService->log(
                module: 'POS',
                action: 'CLOSE_CASHIER_SESSION',
                tableName: 'cashier_sessions',
                recordId: $session->id,
                documentNo: $session->session_no,
                statusBefore: SessionStatus::OPEN->value,
                statusAfter: SessionStatus::CLOSED->value,
                newValues: [
                    'closing_cash'       => $closingCash,
                    'expected_cash'      => $expectedCash,
                    'total_sales'        => $totalSales,
                    'total_transactions' => $totalTransactions,
                    'cash_difference'    => $cashDifference,
                    'status'             => SessionStatus::CLOSED->value,
                ],
            );

            $this->auditService->activity(
                activity: 'CLOSE_SESSION',
                module: 'POS',
                description: "Menutup sesi kasir {$session->session_no}. Total penjualan: Rp " . number_format($totalSales, 0, ',', '.') . ", {$totalTransactions} transaksi.",
            );

            return $session->fresh();
        });
    }
}
