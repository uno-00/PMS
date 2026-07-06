<?php

namespace App\Models\Procurement;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasUuid;

    protected $fillable = ['purchase_order_id', 'item_name', 'description', 'unit', 'quantity', 'unit_cost', 'amount'];

    protected $casts = ['quantity' => 'decimal:2', 'unit_cost' => 'decimal:2', 'amount' => 'decimal:2'];

    protected static function booted(): void
    {
        static::saving(fn (self $i) => $i->amount = round((float) $i->quantity * (float) $i->unit_cost, 2));
        static::saved(fn (self $i) => $i->purchaseOrder?->recalculateTotals());
        static::deleted(fn (self $i) => $i->purchaseOrder?->recalculateTotals());
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
