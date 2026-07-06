<?php

namespace App\Models\Bac;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BacCalendarEvent extends Model
{
    use HasAuditLog, HasUuid;

    protected $fillable = [
        'procurement_id', 'activity_type', 'title', 'scheduled_at', 'venue', 'remarks', 'status', 'created_by',
    ];

    protected $casts = ['scheduled_at' => 'datetime'];

    public const TYPES = [
        'pre_procurement_conference' => 'Pre-Procurement Conference',
        'pre_bid_conference' => 'Pre-Bid Conference',
        'bid_opening' => 'Opening of Bids',
        'post_qualification' => 'Post-Qualification',
        'notice_of_award' => 'Notice of Award',
        'notice_to_proceed' => 'Notice to Proceed',
        'contract_signing' => 'Contract Signing',
        'purchase_order' => 'Purchase Order Issuance',
    ];

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->activity_type] ?? $this->activity_type;
    }
}
