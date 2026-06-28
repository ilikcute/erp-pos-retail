<?php

namespace App\Http\Resources\POS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonthClosingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'closing_year' => $this->closing_year,
            'closing_month' => $this->closing_month,
            'period_label' => $this->period_label,
            'location_id' => $this->location_id,
            'location_name' => $this->location?->name,

            // Ringkasan
            'total_days_closed' => (int) $this->total_days_closed,
            'total_transactions' => (int) $this->total_transactions,
            'total_sales' => (float) $this->total_sales,
            'total_cash' => (float) $this->total_cash,
            'total_non_cash' => (float) $this->total_non_cash,

            // Status
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'notes' => $this->notes,
            'closed_by_name' => $this->closedByUser?->name,
            'closed_at' => $this->closed_at?->format('Y-m-d H:i:s'),
        ];
    }
}
