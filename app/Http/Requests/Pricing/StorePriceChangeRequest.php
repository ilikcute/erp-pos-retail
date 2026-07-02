<?php

namespace App\Http\Requests\Pricing;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('pricing.price-change-request.create');
    }

    public function rules(): array
    {
        return [
            'price_list_id' => ['required', 'integer', 'exists:price_lists,id'],
            'effective_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.unit_id' => ['required', 'integer', 'exists:units,id'],
            'items.*.old_price' => ['required', 'numeric', 'min:0'],
            'items.*.new_price' => ['required', 'numeric', 'min:0'],
            'items.*.change_reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
