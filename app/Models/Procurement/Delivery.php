<?php

namespace App\Models\Procurement;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Delivery extends Model
{
    use HasAuditLog, HasDocuments, HasUuid;

    protected $fillable = ['purchase_order_id', 'delivery_receipt_no', 'delivery_date', 'received_by', 'status', 'remarks'];

    protected $casts = ['delivery_date' => 'date'];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function inspection(): HasOne
    {
        return $this->hasOne(Inspection::class);
    }

    public function acceptance(): HasOne
    {
        return $this->hasOne(Acceptance::class);
    }
}
