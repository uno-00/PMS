<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PpmpItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_name' => $this->item_name,
            'unit' => $this->unit,
            'quantity' => (float) $this->quantity,
            'abc' => (float) $this->abc,
            'utilized_amount' => (float) $this->utilized_amount,
            'remaining_balance' => $this->remainingBalance(),
            'schedule_start' => $this->schedule_start?->toDateString(),
            'schedule_end' => $this->schedule_end?->toDateString(),
        ];
    }
}
