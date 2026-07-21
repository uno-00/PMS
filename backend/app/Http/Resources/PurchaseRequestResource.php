<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pr_no' => $this->pr_no,
            'purpose' => $this->purpose,
            'division' => $this->whenLoaded('division', fn () => $this->division->name),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'total_amount' => (float) $this->total_amount,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
