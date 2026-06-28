<?php

namespace App\Services\POS;

use App\Enums\POS\SessionStatus;
use App\Models\POS\CashierSession;
use App\Models\POS\CashierShift;
use App\Repositories\Contracts\POS\SessionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SessionService
{
    public function __construct(
        private readonly SessionRepositoryInterface $sessionRepo,
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
        $shift = CashierShift::findOrFail($shiftId);
        if (!$shift->is_active) {
            throw new \DomainException('Shift tidak aktif');
        }

        return $this->sessionRepo->create([
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
    }

    /**
     * Tutup sesi kasir
     */
    public function closeSession(
        int $sessionId,
        float $closingCash,
        int $closedBy,
        ?string $notes = null
    ): CashierSession {
        return DB::transaction(function () use ($sessionId, $closingCash, $closedBy, $notes) {
            $session = CashierSession::findOrFail($sessionId);

            if ($session->isClosed()) {
                throw new \DomainException('Sesi sudah ditutup');
            }

            // Hitung expected cash dari transaksi
            $expectedCash = $session->calculateExpectedCash();
            $totalSales = $session->calculateTotalSales();
            $totalTransactions = $session->calculateTotalTransactions();
            $cashDifference = $closingCash - ($session->opening_cash + $expectedCash);

            $session->update([
                'closing_cash' => $closingCash,
                'expected_cash' => $expectedCash,
                'total_sales' => $totalSales,
                'total_transactions' => $totalTransactions,
                'cash_difference' => $cashDifference,
                'status' => SessionStatus::CLOSED,
                'closed_at' => now(),
                'closed_by' => $closedBy,
                'notes' => $notes ? trim($session->notes . "\n" . $notes) : $session->notes,
            ]);

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
        if (!$session || $session->isClosed()) {
            return;
        }

        $session->update([
            'expected_cash' => $session->calculateExpectedCash(),
            'total_sales' => $session->calculateTotalSales(),
            'total_transactions' => $session->calculateTotalTransactions(),
        ]);
    }
}
