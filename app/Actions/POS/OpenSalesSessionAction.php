<?php

namespace App\Actions\POS;

use App\Enums\DocumentStatus;
use App\Models\POS\SalesSession;
use App\Models\POS\Shift;
use App\Repositories\Contracts\POS\SalesSessionRepositoryInterface;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;

class OpenSalesSessionAction
{
    public function __construct(
        private readonly SalesSessionRepositoryInterface $sessionRepository,
        private readonly DocumentNumberService $documentNumberService,
        private readonly AuditService $auditService,
    ) {}

    public function execute(int $shiftId, array $data): SalesSession
    {
        return DB::transaction(function () use ($shiftId, $data) {
            $shift = Shift::findOrFail($shiftId);

            if ($shift->status !== 'OPEN') {
                throw new \RuntimeException('Shift is not open.');
            }

            $sessionNo = $this->documentNumberService->generate('SALES_SESSION');

            $session = $this->sessionRepository->create([
                'session_no'        => $sessionNo,
                'shift_id'          => $shiftId,
                'cashier_id'        => auth()->id(),
                'session_date'      => now()->toDateString(),
                'opening_time'      => now(),
                'opening_balance'   => $data['opening_balance'] ?? 0,
                'status'            => 'OPEN',
                'total_sales'       => 0,
                'transaction_count' => 0,
                'created_by'        => auth()->id(),
                'updated_by'        => auth()->id(),
            ]);

            $this->auditService->log(
                module: 'POS',
                action: 'OPEN_SALES_SESSION',
                tableName: 'sales_sessions',
                recordId: $session->id,
                documentNo: $sessionNo,
                newValues: [
                    'shift_id'        => $shiftId,
                    'cashier_id'      => auth()->id(),
                    'opening_balance' => $data['opening_balance'] ?? 0,
                ],
            );

            return $session;
        });
    }
}
