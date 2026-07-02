<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('product.product.update');
    }

    public function rules(): array
    {
        return [
            'product_name' => ['sometimes', 'required', 'string', 'max:200'],
            'brand_id' => ['nullable', 'integer', 'exists:product_brands,id'],
            'category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
            'base_unit_id' => ['sometimes', 'required', 'integer', 'exists:units,id'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'is_sellable' => ['boolean'],
            'is_purchasable' => ['boolean'],
            'track_stock' => ['boolean'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'max_stock' => ['nullable', 'numeric', 'min:0'],
            'reorder_point' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
