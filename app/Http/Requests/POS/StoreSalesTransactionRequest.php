<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pos.transaction.create');
    }

    public function rules(): array
    {
        return [
            'sales_session_id' => 'required|exists:sales_sessions,id',
            'customer_id'      => 'nullable|exists:customers,id',
            'subtotal'         => 'required|numeric|min:0',
            'discount_amount'  => 'nullable|numeric|min:0',
            'tax_amount'       => 'nullable|numeric|min:0',
            'grand_total'      => 'required|numeric|min:0.01',
            'paid_amount'      => 'required|numeric|min:0',
            'change_amount'    => 'nullable|numeric|min:0',
            'tax_id'           => 'nullable|exists:taxes,id',
            'tax_rate'         => 'nullable|numeric|min:0|max:100',
            'notes'            => 'nullable|string|max:500',

            'items'                  => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.item_name'      => 'required|string|max:200',
            'items.*.sku'            => 'required|string|max:100',
            'items.*.barcode'        => 'nullable|string|max:100',
            'items.*.unit_id'        => 'required|exists:units,id',
            'items.*.quantity'       => 'required|numeric|min:0.01',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_amount'     => 'nullable|numeric|min:0',
            'items.*.line_total'     => 'required|numeric|min:0',
            'items.*.cost_price'     => 'nullable|numeric|min:0',

            'payments'                   => 'required|array|min:1',
            'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
            'payments.*.amount'          => 'required|numeric|min:0.01',
            'payments.*.reference_no'    => 'nullable|string|max:100',
            'payments.*.notes'           => 'nullable|string|max:500',

            'discounts'                        => 'nullable|array',
            'discounts.*.sales_transaction_item_id' => 'nullable|exists:sales_transaction_items,id',
            'discounts.*.discount_type'        => 'required|in:MANUAL,PROMO,VOUCHER,MEMBER',
            'discounts.*.discount_value'       => 'nullable|numeric|min:0',
            'discounts.*.discount_amount'      => 'required|numeric|min:0',
            'discounts.*.promotion_id'         => 'nullable|exists:promotions,id',
            'discounts.*.description'          => 'nullable|string|max:500',
        ];
    }
}
