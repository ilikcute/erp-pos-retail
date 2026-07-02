<?php

namespace App\Services\POS;

use App\Enums\POS\SessionStatus;
use App\Models\POS\CashierSession;
use App\Models\POS\Shift;
use App\Repositories\Contracts\POS\SessionRepositoryInterface;
use App\Support\AuditService;
use Illuminate\Support\Facades\DB;

class SessionService
{
    public function __construct(
        private readonly SessionRepositoryInterface $sessionRepo,
        private readonly AuditService $auditService,
    ) {}

    /**
     * Buka sesi kasir
     */
    public function openSession(
        int $userId,
        int $shiftId,
        float $openingCash,
        ?int $locationId = null,
        ?string $notes = null
    ): CashierSession {
        // Validasi: tidak boleh ada sesi aktif
        if ($this->sessionRepo->hasOpenSession($userId)) {
            throw new \DomainException(
                'Anda sudah memiliki sesi aktif. Tutup sesi sebelumnya terlebih dahulu.'
            );
        }

        // Validasi shift aktif
        $shift = Shift::findOrFail($shiftId);
        if (! $shift->is_active) {
            throw new \DomainException('Shift tidak aktif');
        }

        $session = $this->sessionRepo->create([
            'session_no' => $this->sessionRepo->generateSessionNo(),
            'user_id' => $userId,
            'shift_id' => $shiftId,
            'location_id' => $locationId,
            'opening_cash' => $openingCash,
            'expected_cash' => 0,
            'total_sales' => 0,
            'total_transactions' => 0,
            'status' => SessionStatus::OPEN,
            'notes' => $notes,
            'opened_at' => now(),
        ]);

        // ─── Audit Log ────────────────────────────────────────────────
        $this->auditService->log(
            module: 'POS',
            action: 'OPEN_CASHIER_SESSION',
            tableName: 'cashier_sessions',
            recordId: $session->id,
            documentNo: $session->session_no,
            newValues: [
                'session_no' => $session->session_no,
                'user_id' => $userId,
                'shift_id' => $shiftId,
                'location_id' => $locationId,
                'opening_cash' => $openingCash,
                'status' => SessionStatus::OPEN->value,
            ],
            statusAfter: SessionStatus::OPEN->value,
        );

        // ─── Activity Log ─────────────────────────────────────────────
        $this->auditService->activity(
            activity: 'OPEN_SESSION',
            module: 'POS',
            description: "Membuka sesi kasir {$session->session_no} dengan modal awal Rp ".number_format($openingCash, 0, ',', '.'),
        );

        return $session;
    }

    /**
     * Tutup sesi kasir
     */
    public function closeSession(
        int $sessionId,
        float $closingCash,
        int $closedBy,
        ?string $notes = null,
        float $reimbursementAmount = 0,
        ?string $varianceReason = null
    ): CashierSession {
        return DB::transaction(function () use ($sessionId, $closingCash, $closedBy, $notes, $reimbursementAmount, $varianceReason) {
            $session = CashierSession::findOrFail($sessionId);

            if ($session->isClosed()) {
                throw new \DomainException('Sesi sudah ditutup');
            }

            // Hitung expected cash dari transaksi
            $expectedCash = $session->calculateExpectedCash();
            $totalSales = $session->calculateTotalSales();
            $totalTransactions = $session->calculateTotalTransactions();
            $cashDifference = $closingCash - $expectedCash;

            $session->update([
                'closing_cash' => $closingCash,
                'expected_cash' => $expectedCash,
                'total_sales' => $totalSales,
                'total_transactions' => $totalTransactions,
                'cash_difference' => $cashDifference,
                'reimbursement_amount' => $reimbursementAmount,
                'variance_reason' => $varianceReason,
                'status' => SessionStatus::CLOSED,
                'closed_at' => now(),
                'closed_by' => $closedBy,
                'notes' => $notes ? trim($session->notes."\n".$notes) : $session->notes,
            ]);

            // ─── Audit Log ────────────────────────────────────────────
            $this->auditService->log(
                module: 'POS',
                action: 'CLOSE_CASHIER_SESSION',
                tableName: 'cashier_sessions',
                recordId: $session->id,
                documentNo: $session->session_no,
                statusBefore: SessionStatus::OPEN->value,
                statusAfter: SessionStatus::CLOSED->value,
                newValues: [
                    'closing_cash' => $closingCash,
                    'expected_cash' => $expectedCash,
                    'total_sales' => $totalSales,
                    'total_transactions' => $totalTransactions,
                    'cash_difference' => $cashDifference,
                    'reimbursement_amount' => $reimbursementAmount,
                    'variance_reason' => $varianceReason,
                    'status' => SessionStatus::CLOSED->value,
                ],
            );

            // ─── Activity Log ─────────────────────────────────────────
            $this->auditService->activity(
                activity: 'CLOSE_SESSION',
                module: 'POS',
                description: "Menutup sesi kasir {$session->session_no}. Total penjualan: Rp ".number_format($totalSales, 0, ',', '.').", {$totalTransactions} transaksi.",
            );

            return $session->fresh();
        });
    }

    /**
     * Ambil sesi aktif user
     */
    public function getActiveSession(int $userId): ?CashierSession
    {
        return $this->sessionRepo->findActiveSession($userId);
    }

    /**
     * Refresh statistik sesi (dipanggil setelah transaksi)
     */
    public function refreshSessionStats(int $sessionId): void
    {
        $session = CashierSession::find($sessionId);
        if (! $session || $session->isClosed()) {
            return;
        }

        $session->update([
            'expected_cash' => $session->calculateExpectedCash(),
            'total_sales' => $session->calculateTotalSales(),
            'total_transactions' => $session->calculateTotalTransactions(),
        ]);
    }
}
