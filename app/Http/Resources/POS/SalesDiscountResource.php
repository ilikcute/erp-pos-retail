<?php

namespace App\Http\Resources\POS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesDiscountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'sales_transaction_item_id' => $this->sales_transaction_item_id,
            'discount_type'             => $this->discount_type,
            'discount_value'            => (float) $this->discount_value,
            'discount_amount'           => (float) $this->discount_amount,
            'promotion_id'              => $this->promotion_id,
            'description'               => $this->description,
        ];
    }
}
