<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('product.product.create');
    }

    public function rules(): array
    {
        return [
            'product_code'    => ['required', 'string', 'max:50', 'unique:products,product_code'],
            'product_name'    => ['required', 'string', 'max:200'],
            'product_type'    => ['required', 'in:SIMPLE,VARIANT,BUNDLE'],
            'brand_id'        => ['nullable', 'integer', 'exists:product_brands,id'],
            'category_id'     => ['nullable', 'integer', 'exists:product_categories,id'],
            'base_unit_id'    => ['required', 'integer', 'exists:units,id'],
            'description'     => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'is_active'       => ['boolean'],
            'is_sellable'     => ['boolean'],
            'is_purchasable'  => ['boolean'],
            'track_stock'     => ['boolean'],
            'min_stock'       => ['nullable', 'numeric', 'min:0'],
            'max_stock'       => ['nullable', 'numeric', 'min:0'],
            'reorder_point'   => ['nullable', 'numeric', 'min:0'],
            
            // Default variant (untuk SIMPLE)
            'default_variant'              => ['required_if:product_type,SIMPLE', 'array'],
            'default_variant.sku'          => ['nullable', 'string', 'max:100', 'unique:product_variants,sku'],
            'default_variant.barcode'      => ['nullable', 'string', 'max:100', 'unique:product_barcodes,barcode'],
            'default_variant.barcode_type' => ['nullable', 'in:EAN13,EAN8,QR,CODE128,CUSTOM'],
            'default_variant.weight'       => ['nullable', 'numeric', 'min:0'],
            'default_variant.purchase_price' => ['nullable', 'numeric', 'min:0'],

            // Attributes (untuk VARIANT)
            'attributes'                     => ['required_if:product_type,VARIANT', 'array'],
            'attributes.*.attribute_name'    => ['required', 'string', 'max:100'],
            'attributes.*.values'            => ['required', 'array', 'min:1'],
            'attributes.*.values.*'          => ['required', 'string', 'max:100'],

            // Variants (untuk VARIANT)
            'variants'                       => ['required_if:product_type,VARIANT', 'array', 'min:1'],
            'variants.*.sku'                 => ['required', 'string', 'max:100', 'unique:product_variants,sku'],
            'variants.*.variant_name'        => ['required', 'string', 'max:200'],
            'variants.*.barcode'             => ['nullable', 'string', 'max:100', 'unique:product_barcodes,barcode'],
            'variants.*.barcode_type'        => ['nullable', 'in:EAN13,EAN8,QR,CODE128,CUSTOM'],
            'variants.*.weight'              => ['nullable', 'numeric', 'min:0'],
            'variants.*.purchase_price'      => ['nullable', 'numeric', 'min:0'],
            'variants.*.attribute_values'    => ['required', 'array'],
            'variants.*.attribute_values.*.attribute_name' => ['required', 'string'],
            'variants.*.attribute_values.*.value'          => ['required', 'string'],
        ];
    }
}
