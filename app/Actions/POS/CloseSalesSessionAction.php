<?php

namespace App\Actions\POS;

use App\Enums\DocumentStatus;
use App\Models\POS\SalesSession;
use App\Repositories\Contracts\POS\SalesSessionRepositoryInterface;
use App\Support\AuditService;
use Illuminate\Support\Facades\DB;

class CloseSalesSessionAction
{
    public function __construct(
        private readonly SalesSessionRepositoryInterface $sessionRepository,
        private readonly AuditService $auditService,
    ) {}

    public function execute(SalesSession $session, array $data): SalesSession
    {
        return DB::transaction(function () use ($session, $data) {
            if ($session->status !== 'OPEN') {
                throw new \RuntimeException('Only open sessions can be closed.');
            }

            $postedTransactions = $session->transactions()
                ->where('status', DocumentStatus::POSTED->value)
                ->get();

            $totalSales = $postedTransactions->sum('grand_total');
            $expectedCash = $session->opening_balance + $totalSales;
            $variance = $data['closing_balance'] - $expectedCash;

            $session->update([
                'closing_time'      => now(),
                'closing_balance'   => $data['closing_balance'],
                'total_sales'       => $totalSales,
                'transaction_count' => $postedTransactions->count(),
                'variance_amount'   => $variance,
                'status'            => 'CLOSED',
                'closed_by'         => auth()->id(),
                'closed_at'         => now(),
                'notes'             => $data['notes'] ?? null,
                'updated_by'        => auth()->id(),
            ]);

            $this->auditService->log(
                module: 'POS',
                action: 'CLOSE_SALES_SESSION',
                tableName: 'sales_sessions',
                recordId: $session->id,
                documentNo: $session->session_no,
                newValues: [
                    'closing_balance'   => $data['closing_balance'],
                    'total_sales'       => $totalSales,
                    'transaction_count' => $postedTransactions->count(),
                    'variance_amount'   => $variance,
                    'status'            => 'CLOSED',
                ],
            );

            return $session->fresh();
        });
    }
}
