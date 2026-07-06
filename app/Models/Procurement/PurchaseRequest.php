<?php

namespace App\Models\Procurement;

use App\Enums\PurchaseRequestStatus;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HasWorkflow;
use App\Models\Planning\Ppmp;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseRequest extends Model
{
    use HasAuditLog, HasDocuments, HasUuid, HasWorkflow, SoftDeletes;

    protected $fillable = [
        'pr_no', 'fiscal_year_id', 'division_id', 'ppmp_id', 'purpose', 'total_amount', 'status',
        'requested_by', 'submitted_at', 'division_chief_by', 'division_chief_at', 'planning_by',
        'planning_at', 'budget_by', 'budget_at', 'hope_by', 'hope_at', 'remarks',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'status' => PurchaseRequestStatus::class,
        'submitted_at' => 'datetime',
        'division_chief_at' => 'datetime',
        'planning_at' => 'datetime',
        'budget_at' => 'datetime',
        'hope_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $pr) {
            $pr->pr_no ??= 'PR-'.($pr->fiscalYear?->year ?? now()->year).'-'.str_pad((string) (DB::table('purchase_requests')->count() + 1), 5, '0', STR_PAD_LEFT);
        });
    }

    protected function auditableAttributes(): array
    {
        return ['status', 'total_amount', 'purpose'];
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function ppmp(): BelongsTo
    {
        return $this->belongsTo(Ppmp::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function hopeApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hope_by');
    }

    public function certificateOfAvailabilityOfFunds(): HasOne
    {
        return $this->hasOne(CertificateOfAvailabilityOfFunds::class, 'purchase_request_id');
    }

    public function recalculateTotal(): void
    {
        $this->update(['total_amount' => $this->items()->sum('amount')]);
    }

    public function isEditable(): bool
    {
        return $this->status === PurchaseRequestStatus::Draft;
    }
}
