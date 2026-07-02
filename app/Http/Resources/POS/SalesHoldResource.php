<?php

namespace App\Http\Resources\POS;

use App\Http\Resources\MasterData\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesHoldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hold_no' => $this->hold_no,
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'tax_amount' => (float) $this->tax_amount,
            'grand_total' => (float) $this->grand_total,
            'notes' => $this->notes,
            'held_at' => $this->held_at?->toIso8601String(),
            'resumed_at' => $this->resumed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'items' => SalesHoldItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
