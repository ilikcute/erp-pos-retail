<?php

namespace App\Http\Resources\Pricing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'price_list_code' => $this->price_list_code,
            'price_list_name' => $this->price_list_name,
            'price_list_type' => $this->price_list_type,
            'currency' => $this->currency,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'is_valid' => $this->isValid(),
            'valid_from' => $this->valid_from?->toDateString(),
            'valid_to' => $this->valid_to?->toDateString(),
            'description' => $this->description,
            'customer_categories' => $this->whenLoaded(
                'customerCategories',
                fn () => $this->customerCategories->map(fn ($c) => [
                    'id' => $c->id,
                    'category_name' => $c->category_name,
                ])
            ),
            'items_count' => $this->whenCounted('items'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
