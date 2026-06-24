<?php

namespace App\Actions\Pricing;

use App\Enums\DocumentStatus;
use App\Models\Pricing\PriceChangeRequest;
use App\Models\Pricing\PriceHistory;
use App\Repositories\Contracts\Pricing\PriceChangeRequestRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceListRepositoryInterface;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;

class ApprovePriceChangeRequestAction
{
    public function __construct(
        private readonly PriceChangeRequestRepositoryInterface $priceChangeRepository,
        private readonly PriceListRepositoryInterface $priceListRepository,
        private readonly DocumentNumberService $documentNumberService,
        private readonly AuditService $auditService,
    ) {}

    public function execute(PriceChangeRequest $request, string $approvedBy): PriceChangeRequest
    {
        return DB::transaction(function () use ($request, $approvedBy) {
            if ($request->status !== DocumentStatus::PENDING->value) {
                throw new \RuntimeException('Only pending requests can be approved.');
            }

            $request->update([
                'status'         => DocumentStatus::APPROVED->value,
                'approved_by'    => $approvedBy,
                'approved_at'    => now(),
                'approved_notes' => auth()->id(),
            ]);

            $this->auditService->log(
                module: 'Pricing',
                action: 'APPROVE_PRICE_CHANGE_REQUEST',
                tableName: 'price_change_requests',
                recordId: $request->id,
                documentNo: $request->request_no,
                statusBefore: DocumentStatus::PENDING->value,
                statusAfter: DocumentStatus::APPROVED->value,
            );

            return $request->fresh();
        });
    }
}
