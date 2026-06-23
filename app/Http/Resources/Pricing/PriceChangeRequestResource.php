<?php

namespace App\Http\Resources\Pricing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceChangeRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'request_no'     => $this->request_no,
            'status'         => $this->status,
            'effective_date' => $this->effective_date?->toDateString(),
            'reason'         => $this->reason,
            'notes'          => $this->notes,
            'price_list'     => $this->whenLoaded('priceList', fn() => [
                'id'              => $this->priceList->id,
                'price_list_name' => $this->priceList->price_list_name,
            ]),
            'items'          => $this->whenLoaded(
                'items',
                fn() =>
                $this->items->map(fn($item) => [
                    'id'                 => $item->id,
                    'product_variant_id' => $item->product_variant_id,
                    'unit_id'            => $item->unit_id,
                    'old_price'          => $item->old_price,
                    'new_price'          => $item->new_price,
                    'change_pct'         => $item->change_pct,
                    'change_reason'      => $item->change_reason,
                    'variant'            => $item->relationLoaded('variant') ? [
                        'sku'          => $item->variant->sku,
                        'variant_name' => $item->variant->variant_name,
                    ] : null,
                ])
            ),
            'approved_by'     => $this->approved_by,
            'approved_at'     => $this->approved_at?->toISOString(),
            'rejected_by'     => $this->rejected_by,
            'rejected_at'     => $this->rejected_at?->toISOString(),
            'rejection_reason' => $this->rejection_reason,
            'applied_at'      => $this->applied_at?->toISOString(),
            'created_by'      => $this->created_by,
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),
        ];
    }
}
