<?php

namespace App\Models\Planning;

use App\Enums\PpmpStatus;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HasWorkflow;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ppmp extends Model
{
    use HasAuditLog, HasDocuments, HasUuid, HasWorkflow, SoftDeletes;

    protected $fillable = [
        'fiscal_year_id', 'annual_procurement_plan_id', 'division_id', 'ppmp_type', 'parent_id',
        'revision_number', 'control_no', 'title', 'total_abc', 'status', 'prepared_by', 'submitted_at',
        'division_chief_by', 'division_chief_at', 'planning_by', 'planning_at', 'budget_by', 'budget_at',
        'bac_by', 'bac_at', 'approved_by', 'approved_at', 'locked_by', 'locked_at', 'remarks',
    ];

    protected $casts = [
        'total_abc' => 'decimal:2',
        'status' => PpmpStatus::class,
        'submitted_at' => 'datetime',
        'division_chief_at' => 'datetime',
        'planning_at' => 'datetime',
        'budget_at' => 'datetime',
        'bac_at' => 'datetime',
        'approved_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $ppmp) {
            $ppmp->control_no ??= 'PPMP-'.($ppmp->fiscalYear?->year ?? now()->year).'-'.strtoupper(Str::random(6));
        });
    }

    protected function auditableAttributes(): array
    {
        return ['title', 'status', 'total_abc', 'ppmp_type', 'revision_number'];
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function annualProcurementPlan(): BelongsTo
    {
        return $this->belongsTo(AnnualProcurementPlan::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PpmpItem::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function recalculateTotal(): void
    {
        $this->update(['total_abc' => $this->items()->sum('abc')]);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [PpmpStatus::Draft, PpmpStatus::ReturnedForRevision], true);
    }
}
