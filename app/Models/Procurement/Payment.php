<?php

namespace App\Models\Procurement;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasAuditLog, HasDocuments, HasUuid;

    protected $fillable = ['purchase_order_id', 'or_no', 'amount', 'payment_date', 'method', 'status', 'processed_by'];

    protected $casts = ['amount' => 'decimal:2', 'payment_date' => 'date'];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
