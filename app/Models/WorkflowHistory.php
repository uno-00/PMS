<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Polymorphic, append-only ledger of every workflow status change made
 * to any procurement document (GAA, APP, PPMP, PR, CAF, BAC activities,
 * bids, NOA, NTP, PO, etc). This is the backbone of the system-wide
 * audit trail requirement.
 */
class WorkflowHistory extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'workflowable_type',
        'workflowable_id',
        'from_status',
        'to_status',
        'action',
        'remarks',
        'performed_by',
        'performed_role',
        'metadata',
        'performed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'performed_at' => 'datetime',
    ];

    public function workflowable(): MorphTo
    {
        return $this->morphTo();
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
