<?php

namespace App\Models\Bac;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\Supplier\Bidder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class BidSubmission extends Model
{
    use HasAuditLog, HasDocuments, HasUuid;

    protected $fillable = ['bid_no', 'procurement_id', 'bidder_id', 'version', 'submitted_at', 'is_late', 'status'];

    protected $casts = ['submitted_at' => 'datetime', 'is_late' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(fn (self $bid) => $bid->bid_no ??= 'BID-'.now()->format('Y').'-'.strtoupper(Str::random(8)));
    }

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    public function bidder(): BelongsTo
    {
        return $this->belongsTo(Bidder::class);
    }

    public function evaluation(): HasOne
    {
        return $this->hasOne(BidEvaluation::class);
    }

    public function technicalDocument()
    {
        return $this->latestDocument('technical-document');
    }

    public function financialDocument()
    {
        return $this->latestDocument('financial-document');
    }

    public function eligibilityDocument()
    {
        return $this->latestDocument('eligibility-document');
    }
}
