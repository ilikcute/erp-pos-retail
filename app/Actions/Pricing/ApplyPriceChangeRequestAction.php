<?php

namespace App\Actions\Pricing;

use App\Enums\DocumentStatus;
use App\Models\Pricing\PriceChangeRequest;
use App\Models\Pricing\PriceHistory;
use App\Repositories\Contracts\Pricing\PriceChangeRequestRepositoryInterface;
use App\Repositories\Contracts\Pricing\PriceListRepositoryInterface;
use App\Support\AuditService;
use Illuminate\Support\Facades\DB;

class ApplyPriceChangeRequestAction
{
    public function __construct(
        private readonly PriceChangeRequestRepositoryInterface $priceChangeRepository,
        private readonly PriceListRepositoryInterface $priceListRepository,
        private readonly AuditService $auditService,
    ) {}

    public function execute(PriceChangeRequest $request): PriceChangeRequest
    {
        return DB::transaction(function () use ($request) {
            if ($request->status !== DocumentStatus::APPROVED->value) {
                throw new \RuntimeException('Only approved requests can be applied.');
            }

            $priceList = $this->priceListRepository->findById($request->price_list_id);
            if (!$priceList) {
                throw new \RuntimeException('Price list not found.');
            }

            foreach ($request->items as $item) {
                $priceListItem = $priceList->items()
                    ->where('product_variant_id', $item->product_variant_id)
                    ->where('unit_id', $item->unit_id ?? null)
                    ->first();

                if ($priceListItem) {
                    $oldPrice = $priceListItem->price;

                    $priceListItem->update(['price' => $item->new_price]);

                    PriceHistory::create([
                        'product_variant_id'  => $item->product_variant_id,
                        'price_list_id'       => $priceList->id,
                        'unit_id'             => $item->unit_id,
                        'old_price'           => $oldPrice,
                        'new_price'           => $item->new_price,
                        'change_reason'       => $request->change_reason,
                        'effective_date'      => $request->effective_date,
                        'price_change_request_id' => $request->id,
                        'created_by'          => auth()->id(),
                    ]);
                }
            }

            $request->update([
                'status'      => DocumentStatus::POSTED->value,
                'applied_by'  => auth()->id(),
                'applied_at'  => now(),
            ]);

            $this->auditService->log(
                module: 'Pricing',
                action: 'APPLY_PRICE_CHANGE_REQUEST',
                tableName: 'price_change_requests',
                recordId: $request->id,
                documentNo: $request->request_no,
                statusBefore: DocumentStatus::APPROVED->value,
                statusAfter: DocumentStatus::POSTED->value,
            );

            return $request->fresh();
        });
    }
}
