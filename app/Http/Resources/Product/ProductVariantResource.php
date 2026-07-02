<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'variant_name' => $this->variant_name,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'weight' => $this->weight,
            'purchase_price' => $this->purchase_price,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'product_code' => $this->product->product_code,
                'product_name' => $this->product->product_name,
                'is_sellable' => $this->product->is_sellable,
                'is_active' => $this->product->is_active,
            ]),
            'barcodes' => $this->whenLoaded(
                'barcodes',
                fn () => $this->barcodes->map(fn ($b) => [
                    'id' => $b->id,
                    'barcode' => $b->barcode,
                    'barcode_type' => $b->barcode_type,
                    'is_primary' => $b->is_primary,
                ])
            ),
            'primary_barcode' => $this->whenLoaded(
                'primaryBarcode',
                fn () => $this->primaryBarcode?->barcode
            ),
            'variant_attributes' => $this->whenLoaded(
                'variantAttributes',
                fn () => $this->variantAttributes->map(fn ($va) => [
                    'attribute' => $va->attribute?->attribute_name,
                    'value' => $va->value?->value,
                ])
            ),
            'cost_profile' => $this->whenLoaded(
                'costProfile',
                fn () => $this->costProfile
                    ? [
                        'cost_method' => $this->costProfile->cost_method,
                        'standard_cost' => $this->costProfile->standard_cost,
                        'average_cost' => $this->costProfile->average_cost,
                        'last_cost' => $this->costProfile->last_cost,
                    ] : null
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
