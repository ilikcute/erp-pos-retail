<?php

namespace App\Http\Requests\Pricing;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePriceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('pricing.price-list.manage');
    }

    public function rules(): array
    {
        return [
            'price_list_name' => ['sometimes', 'required', 'string', 'max:150'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'description' => ['nullable', 'string', 'max:255'],
            'customer_category_ids' => ['nullable', 'array'],
            'customer_category_ids.*' => ['integer', 'exists:customer_categories,id'],
        ];
    }
}
