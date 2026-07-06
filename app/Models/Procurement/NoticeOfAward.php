<?php

namespace App\Models\Procurement;

use App\Enums\NoticeOfAwardStatus;
use App\Models\Bac\Procurement;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HasWorkflow;
use App\Models\Supplier\Bidder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NoticeOfAward extends Model
{
    use HasAuditLog, HasDocuments, HasUuid, HasWorkflow;

    protected $table = 'notice_of_awards';

    protected $fillable = [
        'noa_no', 'procurement_id', 'bidder_id', 'amount', 'issued_at', 'status',
        'approved_by', 'responded_at', 'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issued_at' => 'datetime',
        'responded_at' => 'datetime',
        'status' => NoticeOfAwardStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $noa) => $noa->noa_no ??= 'NOA-'.now()->format('Y').'-'.strtoupper(Str::random(6)));
    }

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    public function bidder(): BelongsTo
    {
        return $this->belongsTo(Bidder::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
