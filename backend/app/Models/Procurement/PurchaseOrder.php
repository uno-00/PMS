<?php

namespace App\Models\Procurement;

use App\Enums\PurchaseOrderStatus;
use App\Models\Bac\Procurement;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HasWorkflow;
use App\Models\Settings\ModeOfProcurement;
use App\Models\Supplier\Bidder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PurchaseOrder extends Model
{
    use HasAuditLog, HasDocuments, HasFactory, HasUuid, HasWorkflow;

    protected $fillable = [
        'po_no', 'procurement_id', 'purchase_request_id', 'bidder_id', 'mode_of_procurement_id',
        'subtotal', 'tax_amount', 'total_amount', 'delivery_date', 'delivery_place', 'status',
        'prepared_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'delivery_date' => 'date',
        'approved_at' => 'datetime',
        'status' => PurchaseOrderStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $po) => $po->po_no ??= 'PO-'.now()->format('Y').'-'.strtoupper(Str::random(6)));
    }

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function bidder(): BelongsTo
    {
        return $this->belongsTo(Bidder::class);
    }

    public function modeOfProcurement(): BelongsTo
    {
        return $this->belongsTo(ModeOfProcurement::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function recalculateTotals(float $taxRate = 0.0): void
    {
        $subtotal = $this->items()->sum('amount');
        $tax = round($subtotal * $taxRate, 2);
        $this->update(['subtotal' => $subtotal, 'tax_amount' => $tax, 'total_amount' => $subtotal + $tax]);
    }
}
