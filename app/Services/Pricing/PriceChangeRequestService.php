<?php

namespace App\Services\Pricing;

use App\Models\Pricing\PriceChangeRequest;
use App\Repositories\Contracts\Pricing\PriceChangeRequestRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceListItemRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceHistoryRepositoryInterface;
use App\Enums\PriceChangeRequestStatus;
use App\Support\AuditService;
use App\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;

class PriceChangeRequestService
{
    public function __construct(
        private readonly PriceChangeRequestRepositoryInterface $priceChangeRequestRepository,
        private readonly PriceListItemRepositoryInterface $priceListItemRepository,
        private readonly PriceHistoryRepositoryInterface $priceHistoryRepository,
        private readonly AuditService $auditService,
        private readonly DocumentNumberService $docNumberService,
    ) {}

    public function create(array $data): PriceChangeRequest
    {
        $data['request_no'] = $this->docNumberService->generate('PRICE_CHANGE_REQUEST');
        $data['status']     = PriceChangeRequestStatus::DRAFT->value;

        $items = $data['items'] ?? [];
        unset($data['items']);

        return DB::transaction(function () use ($data, $items) {
            $request = $this->priceChangeRequestRepository->create($data);

            foreach ($items as $item) {
                $request->items()->create($item);
            }

            $this->auditService->log(
                module: 'Pricing',
                action: 'CREATE_PRICE_CHANGE_REQUEST',
                tableName: 'price_change_requests',
                recordId: $request->id,
                documentNo: $request->request_no,
            );

            return $request->load('items');
        });
    }

    public function submit(PriceChangeRequest $request): PriceChangeRequest
    {
        abort_if(! $request->canBeSubmitted(), 422, 'Request tidak bisa disubmit.');

        $request = $this->priceChangeRequestRepository->update($request, ['status' => PriceChangeRequestStatus::PENDING->value]);

        $this->auditService->log(
            module: 'Pricing',
            action: 'SUBMIT_PRICE_CHANGE_REQUEST',
            tableName: 'price_change_requests',
            recordId: $request->id,
            documentNo: $request->request_no,
            statusBefore: PriceChangeRequestStatus::DRAFT->value,
            statusAfter: PriceChangeRequestStatus::PENDING->value,
        );

        return $request;
    }

    public function approve(PriceChangeRequest $request, int $approverId): PriceChangeRequest
    {
        abort_if(
            ! $request->status->canBeApproved(),
            422,
            'Request tidak dalam status PENDING.'
        );

        $request->markAsApproved($approverId);

        $this->auditService->log(
            module: 'Pricing',
            action: 'APPROVE_PRICE_CHANGE_REQUEST',
            tableName: 'price_change_requests',
            recordId: $request->id,
            documentNo: $request->request_no,
            statusBefore: PriceChangeRequestStatus::PENDING->value,
            statusAfter: PriceChangeRequestStatus::APPROVED->value,
        );

        return $request->fresh();
    }

    public function reject(PriceChangeRequest $request, int $rejectorId, string $reason): PriceChangeRequest
    {
        abort_if(
            ! $request->status->canBeApproved(),
            422,
            'Request tidak dalam status PENDING.'
        );

        $request->markAsRejected($rejectorId, $reason);

        $this->auditService->log(
            module: 'Pricing',
            action: 'REJECT_PRICE_CHANGE_REQUEST',
            tableName: 'price_change_requests',
            recordId: $request->id,
            documentNo: $request->request_no,
            statusBefore: PriceChangeRequestStatus::PENDING->value,
            statusAfter: PriceChangeRequestStatus::DRAFT->value,
        );

        return $request->fresh();
    }

    /**
     * Terapkan perubahan harga ke price list items.
     * Setiap perubahan dicatat di price_histories.
     */
    public function apply(PriceChangeRequest $request, int $appliedBy): PriceChangeRequest
    {
        abort_if(
            ! $request->status->canBeApplied(),
            422,
            'Request harus berstatus APPROVED sebelum diterapkan.'
        );

        DB::transaction(function () use ($request, $appliedBy) {
            foreach ($request->items as $item) {
                // Cari item lama
                $existingItem = $this->priceListItemRepository->findItem(
                    priceListId: $request->price_list_id,
                    variantId: $item->product_variant_id,
                    unitId: $item->unit_id,
                    qty: 1.0 // default qty 1
                );

                $oldPrice = $existingItem ? $existingItem->price : 0;

                // Update atau buat item
                $this->priceListItemRepository->createOrUpdate(
                    [
                        'price_list_id'      => $request->price_list_id,
                        'product_variant_id' => $item->product_variant_id,
                        'unit_id'            => $item->unit_id,
                        'min_qty'            => 1.0, // default min_qty
                    ],
                    [
                        'price'      => $item->new_price,
                        'updated_by' => $appliedBy,
                    ]
                );

                // Catat ke history — immutable
                $this->priceHistoryRepository->create([
                    'price_list_id'           => $request->price_list_id,
                    'product_variant_id'       => $item->product_variant_id,
                    'unit_id'                  => $item->unit_id,
                    'old_price'                => $oldPrice,
                    'new_price'                => $item->new_price,
                    'changed_by'               => $appliedBy,
                    'change_source'            => 'PRICE_CHANGE_REQUEST',
                    'price_change_request_id'  => $request->id,
                    'changed_at'               => now(),
                ]);
            }

            $this->priceChangeRequestRepository->update($request, [
                'status'     => PriceChangeRequestStatus::APPLIED->value,
                'applied_at' => now(),
            ]);
        });

        $this->auditService->log(
            module: 'Pricing',
            action: 'APPLY_PRICE_CHANGE_REQUEST',
            tableName: 'price_change_requests',
            recordId: $request->id,
            documentNo: $request->request_no,
            statusBefore: PriceChangeRequestStatus::APPROVED->value,
            statusAfter: PriceChangeRequestStatus::APPLIED->value,
        );

        return $request->fresh();
    }
}
