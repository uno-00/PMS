<?php

namespace App\Models\Procurement;

use App\Models\Concerns\HasUuid;
use App\Models\Planning\PpmpItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestItem extends Model
{
    use HasUuid;

    protected $fillable = [
        'purchase_request_id', 'ppmp_item_id', 'item_name', 'description', 'unit', 'quantity', 'unit_cost', 'amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            $item->amount = round((float) $item->quantity * (float) $item->unit_cost, 2);
        });

        static::saved(fn (self $item) => $item->purchaseRequest?->recalculateTotal());
        static::deleted(fn (self $item) => $item->purchaseRequest?->recalculateTotal());
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function ppmpItem(): BelongsTo
    {
        return $this->belongsTo(PpmpItem::class);
    }
}
