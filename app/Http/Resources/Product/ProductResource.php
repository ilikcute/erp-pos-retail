<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'product_code'     => $this->product_code,
            'product_name'     => $this->product_name,
            'product_type'     => $this->product_type,
            'is_active'        => $this->is_active,
            'is_sellable'      => $this->is_sellable,
            'is_purchasable'   => $this->is_purchasable,
            'track_stock'      => $this->track_stock,
            'min_stock'        => $this->min_stock,
            'max_stock'        => $this->max_stock,
            'reorder_point'    => $this->reorder_point,
            'short_description' => $this->short_description,
            'description'      => $this->description,
            'brand'            => $this->whenLoaded('brand', fn() => [
                'id'         => $this->brand->id,
                'brand_name' => $this->brand->brand_name,
            ]),
            'category'         => $this->whenLoaded('category', fn() => [
                'id'            => $this->category->id,
                'category_name' => $this->category->category_name,
            ]),
            'variants'         => $this->whenLoaded(
                'variants',
                fn() => ProductVariantResource::collection($this->variants)
            ),
            'default_variant'  => $this->whenLoaded(
                'defaultVariant',
                fn() => $this->defaultVariant
                    ? new ProductVariantResource($this->defaultVariant)
                    : null
            ),
            'images'           => $this->whenLoaded(
                'images',
                fn() =>
                $this->images->map(fn($img) => [
                    'id'         => $img->id,
                    'image_path' => $img->image_path,
                    'is_primary' => $img->is_primary,
                    'sort_order' => $img->sort_order,
                ])
            ),
            'attributes'       => $this->whenLoaded(
                'attributes',
                fn() =>
                $this->attributes->map(fn($attr) => [
                    'id'             => $attr->id,
                    'attribute_name' => $attr->attribute_name,
                    'values'         => $attr->relationLoaded('values')
                        ? $attr->values->map(fn($v) => ['id' => $v->id, 'value' => $v->value])
                        : [],
                ])
            ),
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
