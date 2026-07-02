<?php

namespace App\Services\POS;

use App\Enums\POS\ClosingStatus;
use App\Enums\POS\SessionStatus;
use App\Models\POS\CashierSession;
use App\Models\POS\DayClosing;
use App\Models\POS\Transaction;
use App\Repositories\Contracts\POS\DayClosingRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DayClosingService
{
    public function __construct(
        private readonly DayClosingRepositoryInterface $dayClosingRepo,
    ) {}

    /**
     * Eksekusi tutup harian
     *
     * Validasi:
     * 1. Tanggal tidak boleh di masa depan
     * 2. Semua sesi kasir di tanggal tersebut harus sudah CLOSED
     * 3. Belum ada day closing untuk tanggal tersebut
     */
    public function closeDay(
        string $closingDate,
        int $closedBy,
        ?int $locationId = null,
        ?string $notes = null
    ): DayClosing {
        return DB::transaction(function () use ($closingDate, $closedBy, $locationId, $notes) {
            $date = Carbon::parse($closingDate);

            // Validasi 1: Tanggal tidak boleh di masa depan
            if ($date->isFuture()) {
                throw new \DomainException('Tidak bisa menutup hari di masa depan');
            }

            // Validasi 2: Cek apakah sudah ada day closing untuk tanggal ini
            $existing = $this->dayClosingRepo->findByDate($closingDate, $locationId);
            if ($existing && $existing->isClosed()) {
                throw new \DomainException("Hari {$closingDate} sudah ditutup");
            }

            // Validasi 3: Semua sesi kasir di tanggal tersebut harus CLOSED
            $openSessions = CashierSession::whereDate('opened_at', $closingDate)
                ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
                ->where('status', SessionStatus::OPEN)
                ->count();

            if ($openSessions > 0) {
                throw new \DomainException(
                    "Masih ada {$openSessions} sesi kasir yang belum ditutup. ".
                        'Tutup semua sesi terlebih dahulu.'
                );
            }

            // ═══════════════════════════════════════════════════════════
            // HITUNG RINGKASAN TRANSAKSI
            // ═══════════════════════════════════════════════════════════
            $transactionsQuery = Transaction::whereDate('created_at', $closingDate)
                ->when($locationId, fn ($q) => $q->whereHas(
                    'session',
                    fn ($sq) => $sq->where('location_id', $locationId)
                ));

            $totalTransactions = (clone $transactionsQuery)->count();
            $totalSales = (clone $transactionsQuery)->sum('grand_total');
            $totalCash = (clone $transactionsQuery)
                ->where('payment_method', 'cash')
                ->where('pay_later', false)
                ->sum('cash');
            $totalNonCash = $totalSales - $totalCash;
            $totalDiscount = (clone $transactionsQuery)->sum('discount');
            $totalTax = (clone $transactionsQuery)->sum('tax');

            // ═══════════════════════════════════════════════════════════
            // HITUNG CASH RECONCILIATION
            // ═══════════════════════════════════════════════════════════
            $sessions = CashierSession::whereDate('opened_at', $closingDate)
                ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
                ->where('status', SessionStatus::CLOSED)
                ->get();

            $totalOpeningCash = $sessions->sum('opening_cash');
            $totalClosingCash = $sessions->sum('closing_cash');
            $totalExpectedCash = $sessions->sum('expected_cash');
            $cashDifference = $sessions->sum('cash_difference');

            // ═══════════════════════════════════════════════════════════
            // BUAT / UPDATE DAY CLOSING
            // ═══════════════════════════════════════════════════════════
            $dayClosing = $existing ?? DayClosing::create([
                'closing_date' => $closingDate,
                'closing_number' => $this->dayClosingRepo->generateClosingNumber(),
                'location_id' => $locationId,
                'status' => ClosingStatus::OPEN,
                'closed_by' => $closedBy,
            ]);

            $dayClosing->update([
                'total_transactions' => $totalTransactions,
                'total_sales' => $totalSales,
                'total_cash' => $totalCash,
                'total_non_cash' => $totalNonCash,
                'total_discount' => $totalDiscount,
                'total_tax' => $totalTax,
                'total_opening_cash' => $totalOpeningCash,
                'total_closing_cash' => $totalClosingCash,
                'total_expected_cash' => $totalExpectedCash,
                'cash_difference' => $cashDifference,
                'status' => ClosingStatus::CLOSED,
                'notes' => $notes,
                'closed_by' => $closedBy,
                'closed_at' => now(),
            ]);

            // ═══════════════════════════════════════════════════════════
            // SYNC SESI KE DAY CLOSING (pivot table)
            // ═══════════════════════════════════════════════════════════
            $syncData = [];
            foreach ($sessions as $session) {
                $syncData[$session->id] = [
                    'session_sales' => $session->total_sales,
                    'session_cash' => $session->expected_cash,
                    'session_transactions' => $session->total_transactions,
                ];
            }
            $dayClosing->sessions()->sync($syncData);

            return $dayClosing->fresh(['sessions.user', 'closedByUser', 'location']);
        });
    }

    /**
     * Cek apakah transaksi boleh dibuat pada tanggal tertentu
     */
    public function canTransactOnDate(string $date, ?int $locationId = null): array
    {
        $dayClosing = $this->dayClosingRepo->findByDate($date, $locationId);

        if ($dayClosing && $dayClosing->isClosed()) {
            return [
                'allowed' => false,
                'reason' => "Hari {$date} sudah ditutup. Buka hari baru terlebih dahulu.",
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Ambil statistik hari ini (real-time)
     */
    public function getTodayStats(?int $locationId = null): array
    {
        $today = now()->toDateString();

        $transactionsQuery = Transaction::whereDate('created_at', $today)
            ->when($locationId, fn ($q) => $q->whereHas(
                'session',
                fn ($sq) => $sq->where('location_id', $locationId)
            ));

        return [
            'date' => $today,
            'total_transactions' => (clone $transactionsQuery)->count(),
            'total_sales' => (float) (clone $transactionsQuery)->sum('grand_total'),
            'total_cash' => (float) (clone $transactionsQuery)
                ->where('payment_method', 'cash')
                ->where('pay_later', false)
                ->sum('cash'),
            'total_non_cash' => (float) (clone $transactionsQuery)->sum('grand_total')
                - (clone $transactionsQuery)->where('payment_method', 'cash')->where('pay_later', false)->sum('cash'),
            'open_sessions' => CashierSession::whereDate('opened_at', $today)
                ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
                ->where('status', SessionStatus::OPEN)
                ->count(),
        ];
    }
}
