<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PpmpResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'control_no' => $this->control_no,
            'title' => $this->title,
            'ppmp_type' => $this->ppmp_type,
            'division' => $this->whenLoaded('division', fn () => $this->division->name),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'total_abc' => (float) $this->total_abc,
            'items_count' => $this->whenCounted('items'),
            'items' => PpmpItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
