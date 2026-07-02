<?php

namespace App\Http\Resources\POS;

use App\Http\Resources\MasterData\UnitResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesHoldItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_variant_id' => $this->product_variant_id,
            'product_id' => $this->product_id,
            'item_name' => $this->item_name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'discount_amount' => (float) $this->discount_amount,
            'tax_amount' => (float) $this->tax_amount,
            'line_total' => (float) $this->line_total,
            'unit' => new UnitResource($this->whenLoaded('unit')),
        ];
    }
}
