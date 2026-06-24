<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class StoreHoldBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pos.hold.create');
    }

    public function rules(): array
    {
        return [
            'sales_session_id' => 'required|exists:sales_sessions,id',
            'customer_id'      => 'nullable|exists:customers,id',
            'subtotal'         => 'nullable|numeric|min:0',
            'discount_amount'  => 'nullable|numeric|min:0',
            'tax_amount'       => 'nullable|numeric|min:0',
            'grand_total'      => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string|max:500',

            'items'                     => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.product_id'        => 'required|exists:products,id',
            'items.*.item_name'         => 'required|string|max:200',
            'items.*.sku'               => 'required|string|max:100',
            'items.*.barcode'           => 'nullable|string|max:100',
            'items.*.unit_id'           => 'required|exists:units,id',
            'items.*.quantity'          => 'required|numeric|min:0.01',
            'items.*.unit_price'        => 'required|numeric|min:0',
            'items.*.discount_amount'   => 'nullable|numeric|min:0',
            'items.*.tax_amount'        => 'nullable|numeric|min:0',
            'items.*.line_total'        => 'required|numeric|min:0',
        ];
    }
}
