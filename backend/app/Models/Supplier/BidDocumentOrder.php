<?php

namespace App\Models\Supplier;

use App\Models\Bac\Procurement;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BidDocumentOrder extends Model
{
    use HasAuditLog, HasUuid;

    protected $fillable = ['order_no', 'procurement_id', 'bidder_id', 'amount', 'payment_status', 'or_no', 'paid_at'];

    protected $casts = ['amount' => 'decimal:2', 'paid_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(fn (self $order) => $order->order_no ??= 'BDO-'.now()->format('Y').'-'.strtoupper(Str::random(8)));
    }

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    public function bidder(): BelongsTo
    {
        return $this->belongsTo(Bidder::class);
    }

    public function markPaid(string $orNo): void
    {
        $this->update(['payment_status' => 'paid', 'or_no' => $orNo, 'paid_at' => now()]);
    }
}
