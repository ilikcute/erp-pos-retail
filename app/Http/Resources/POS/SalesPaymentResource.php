<?php

namespace App\Http\Resources\POS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'payment_no'        => $this->payment_no,
            'payment_method_id' => $this->payment_method_id,
            'amount'            => (float) $this->amount,
            'reference_no'      => $this->reference_no,
            'status'            => $this->status,
            'notes'             => $this->notes,
            'posted_at'         => $this->posted_at?->toIso8601String(),
            'payment_method'    => $this->whenLoaded('paymentMethod', fn() => [
                'id'   => $this->paymentMethod->id,
                'name' => $this->paymentMethod->name,
                'type' => $this->paymentMethod->type,
            ]),
        ];
    }
}
