<?php

namespace App\Http\Resources\POS;

use App\Http\Resources\MasterData\CustomerResource;
use App\Http\Resources\System\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_no' => $this->transaction_no,
            'transaction_date' => $this->transaction_date?->format('Y-m-d'),
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'tax_amount' => (float) $this->tax_amount,
            'grand_total' => (float) $this->grand_total,
            'paid_amount' => (float) $this->paid_amount,
            'change_amount' => (float) $this->change_amount,
            'tax_rate' => (float) $this->tax_rate,
            'notes' => $this->notes,
            'posted_at' => $this->posted_at?->toIso8601String(),
            'voided_at' => $this->voided_at?->toIso8601String(),
            'void_reason' => $this->void_reason,
            'created_at' => $this->created_at?->toIso8601String(),
            'cashier' => new UserResource($this->whenLoaded('cashier')),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'items' => SalesTransactionItemResource::collection($this->whenLoaded('items')),
            'payments' => SalesPaymentResource::collection($this->whenLoaded('payments')),
            'discounts' => SalesDiscountResource::collection($this->whenLoaded('discounts')),
        ];
    }
}
