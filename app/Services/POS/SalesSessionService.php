<?php

namespace App\Services\POS;

use App\Models\POS\SalesSession;
use App\Repositories\Contracts\POS\SalesSessionRepositoryInterface;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SalesSessionService
{
    public function __construct(
        private readonly SalesSessionRepositoryInterface $sessionRepository,
        private readonly DocumentNumberService $documentNumberService,
        private readonly AuditService $auditService,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->sessionRepository->paginate($filters, $perPage);
    }

    public function findById(int $id): ?SalesSession
    {
        return $this->sessionRepository->findById($id);
    }

    public function findOpenByCashier(int $cashierId): ?SalesSession
    {
        return $this->sessionRepository->findOpenByCashier($cashierId);
    }

    public function openSession(int $shiftId, int $cashierId, float $openingCash, ?string $notes = null): SalesSession
    {
        $existingOpen = $this->sessionRepository->findOpenByCashier($cashierId);
        if ($existingOpen) {
            throw new \RuntimeException('Cashier already has an open session.');
        }

        $sessionNo = $this->documentNumberService->generate('SALES_SESSION');

        $session = $this->sessionRepository->create([
            'session_no' => $sessionNo,
            'shift_id' => $shiftId,
            'cashier_id' => $cashierId,
            'session_date' => now()->toDateString(),
            'status' => 'OPEN',
            'opening_cash' => $openingCash,
            'notes' => $notes,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->auditService->log(
            module: 'POS',
            action: 'OPEN_SESSION',
            tableName: 'sales_sessions',
            recordId: $session->id,
            documentNo: $sessionNo,
            newValues: ['opening_cash' => $openingCash, 'shift_id' => $shiftId],
        );

        return $session;
    }

    public function closeSession(SalesSession $session, float $closingCash, ?string $notes = null): SalesSession
    {
        if (! $session->isOpen()) {
            throw new \RuntimeException('Session is not open.');
        }

        $totalTransactions = $session->transactions()->where('status', 'POSTED')->sum('grand_total');
        $expectedCash = $session->opening_cash + $totalTransactions;
        $cashDifference = $closingCash - $expectedCash;

        $session = $this->sessionRepository->closeSession($session, [
            'closing_cash' => $closingCash,
            'expected_cash' => $expectedCash,
            'cash_difference' => $cashDifference,
            'total_sales' => $totalTransactions,
            'total_transactions' => $totalTransactions,
            'transaction_count' => $session->transactions()->where('status', 'POSTED')->count(),
            'notes' => $notes,
            'updated_by' => auth()->id(),
        ]);

        $this->auditService->log(
            module: 'POS',
            action: 'CLOSE_SESSION',
            tableName: 'sales_sessions',
            recordId: $session->id,
            documentNo: $session->session_no,
            newValues: [
                'closing_cash' => $closingCash,
                'expected_cash' => $expectedCash,
                'cash_difference' => $cashDifference,
                'total_sales' => $totalTransactions,
            ],
        );

        return $session;
    }
}
