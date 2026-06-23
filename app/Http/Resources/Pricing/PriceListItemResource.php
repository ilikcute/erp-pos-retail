<?php

namespace App\Http\Resources\Pricing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceListItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'price_list_id'      => $this->price_list_id,
            'product_variant_id' => $this->product_variant_id,
            'unit_id'            => $this->unit_id,
            'price'              => $this->price,
            'min_qty'            => $this->min_qty,
            'variant'            => $this->whenLoaded('variant', fn() => [
                'id'           => $this->variant->id,
                'sku'          => $this->variant->sku,
                'variant_name' => $this->variant->variant_name,
                'product'      => $this->variant->relationLoaded('product') ? [
                    'id'           => $this->variant->product->id,
                    'product_name' => $this->variant->product->product_name,
                ] : null,
            ]),
            'updated_at'         => $this->updated_at?->toISOString(),
        ];
    }
}
