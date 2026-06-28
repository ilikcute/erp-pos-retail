<?php

namespace App\Services\POS;

use App\Enums\POS\ClosingStatus;
use App\Models\POS\DayClosing;
use App\Models\POS\MonthClosing;
use App\Repositories\Contracts\POS\MonthClosingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class MonthClosingService
{
    public function __construct(
        private readonly MonthClosingRepositoryInterface $monthClosingRepo,
    ) {}

    /**
     * Eksekusi tutup bulanan
     * 
     * Validasi:
     * 1. Periode tidak boleh di masa depan
     * 2. Semua hari di bulan tersebut harus sudah CLOSED
     * 3. Belum ada month closing untuk periode tersebut
     */
    public function closeMonth(
        int $year,
        int $month,
        int $closedBy,
        ?int $locationId = null,
        ?string $notes = null
    ): MonthClosing {
        return DB::transaction(function () use ($year, $month, $closedBy, $locationId, $notes) {
            // Validasi 1: Periode tidak boleh di masa depan
            $periodDate = \Carbon\Carbon::createFromDate($year, $month, 1);
            if ($periodDate->isFuture()) {
                throw new \DomainException('Tidak bisa menutup periode di masa depan');
            }

            // Validasi 2: Cek apakah sudah ada month closing
            $existing = $this->monthClosingRepo->findByPeriod($year, $month, $locationId);
            if ($existing && $existing->isLocked()) {
                throw new \DomainException("Periode {$month}/{$year} sudah dikunci");
            }

            // Validasi 3: Semua hari di bulan tersebut harus CLOSED
            $daysInMonth = $periodDate->daysInMonth;
            $today = now()->startOfDay();
            $maxDate = $periodDate->copy()->endOfMonth()->startOfDay();

            // Jika bulan ini, hanya cek sampai hari ini
            $lastDateToCheck = $maxDate->greaterThan($today) ? $today : $maxDate;

            $openDays = DayClosing::whereYear('closing_date', $year)
                ->whereMonth('closing_date', $month)
                ->whereDate('closing_date', '<=', $lastDateToCheck)
                ->when($locationId, fn($q) => $q->where('location_id', $locationId))
                ->where('status', '!=', ClosingStatus::CLOSED)
                ->count();

            if ($openDays > 0) {
                throw new \DomainException(
                    "Masih ada {$openDays} hari yang belum ditutup. " .
                        "Tutup semua hari terlebih dahulu."
                );
            }

            // ═══════════════════════════════════════════════════════════
            // HITUNG RINGKASAN BULANAN
            // ═══════════════════════════════════════════════════════════
            $dayClosings = DayClosing::whereYear('closing_date', $year)
                ->whereMonth('closing_date', $month)
                ->where('status', ClosingStatus::CLOSED)
                ->when($locationId, fn($q) => $q->where('location_id', $locationId))
                ->get();

            $totalDaysClosed = $dayClosings->count();
            $totalTransactions = $dayClosings->sum('total_transactions');
            $totalSales = $dayClosings->sum('total_sales');
            $totalCash = $dayClosings->sum('total_cash');
            $totalNonCash = $dayClosings->sum('total_non_cash');

            // ═══════════════════════════════════════════════════════════
            // BUAT / UPDATE MONTH CLOSING
            // ═══════════════════════════════════════════════════════════
            $monthClosing = $existing ?? MonthClosing::create([
                'closing_year' => $year,
                'closing_month' => $month,
                'location_id' => $locationId,
                'status' => ClosingStatus::OPEN,
            ]);

            $monthClosing->update([
                'total_days_closed' => $totalDaysClosed,
                'total_transactions' => $totalTransactions,
                'total_sales' => $totalSales,
                'total_cash' => $totalCash,
                'total_non_cash' => $totalNonCash,
                'status' => ClosingStatus::LOCKED,
                'notes' => $notes,
                'closed_by' => $closedBy,
                'closed_at' => now(),
            ]);

            return $monthClosing->fresh(['closedByUser', 'location']);
        });
    }

    /**
     * Cek apakah periode bulan tertentu boleh ditransaksikan
     */
    public function canTransactInPeriod(int $year, int $month, ?int $locationId = null): array
    {
        if ($this->monthClosingRepo->isPeriodLocked($year, $month, $locationId)) {
            return [
                'allowed' => false,
                'reason' => "Periode {$month}/{$year} sudah ditutup dan dikunci",
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Ambil status periode bulan tertentu
     */
    public function getPeriodStatus(int $year, int $month, ?int $locationId = null): array
    {
        $closing = $this->monthClosingRepo->findByPeriod($year, $month, $locationId);

        if (!$closing) {
            return [
                'closing_year' => $year,
                'closing_month' => $month,
                'status' => 'OPEN',
                'status_label' => 'Buka',
                'notes' => null,
                'total_days_closed' => 0,
                'total_transactions' => 0,
                'total_sales' => 0,
            ];
        }

        return [
            'closing_year' => $closing->closing_year,
            'closing_month' => $closing->closing_month,
            'status' => $closing->status->value,
            'status_label' => $closing->status->label(),
            'notes' => $closing->notes,
            'total_days_closed' => $closing->total_days_closed,
            'total_transactions' => $closing->total_transactions,
            'total_sales' => (float) $closing->total_sales,
            'closed_at' => $closing->closed_at?->format('Y-m-d H:i:s'),
            'closed_by_name' => $closing->closedByUser?->name,
        ];
    }
}
