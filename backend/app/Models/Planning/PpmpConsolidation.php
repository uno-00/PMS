<?php

namespace App\Models\Planning;

use App\Enums\PpmpConsolidationStatus;
use App\Enums\PpmpDocumentType;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HasWorkflow;
use App\Models\Settings\FiscalYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpmpConsolidation extends Model
{
    use HasAuditLog, HasUuid, HasWorkflow, SoftDeletes;

    protected $fillable = [
        'fiscal_year_id', 'parent_id', 'reference_no', 'title', 'document_type',
        'status', 'current_step', 'version_number', 'total_budget', 'validation_issues', 'remarks',
        'created_by', 'planning_by', 'planning_at', 'budget_by', 'budget_at',
        'accounting_by', 'accounting_at', 'bac_by', 'bac_at', 'hope_by', 'hope_at',
        'approved_by', 'approved_at', 'locked_by', 'locked_at',
    ];

    protected $casts = [
        'document_type' => PpmpDocumentType::class,
        'status' => PpmpConsolidationStatus::class,
        'current_step' => 'integer',
        'version_number' => 'integer',
        'total_budget' => 'decimal:2',
        'validation_issues' => 'array',
        'planning_at' => 'datetime',
        'budget_at' => 'datetime',
        'accounting_at' => 'datetime',
        'bac_at' => 'datetime',
        'hope_at' => 'datetime',
        'approved_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    protected function auditableAttributes(): array
    {
        return ['title', 'status', 'document_type', 'total_budget', 'current_step', 'version_number', 'reference_no'];
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sourcePpmps(): BelongsToMany
    {
        return $this->belongsToMany(Ppmp::class, 'ppmp_consolidation_sources')
            ->withTimestamps();
    }

    public function sources(): HasMany
    {
        return $this->hasMany(PpmpConsolidationSource::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PpmpConsolidationItem::class)->orderBy('sort_order')->orderBy('item_no');
    }

    public function bp2020Lines(): HasMany
    {
        return $this->hasMany(PpmpConsolidationBp2020Line::class)->orderBy('sort_order');
    }

    public function wfpLines(): HasMany
    {
        return $this->hasMany(PpmpConsolidationWfpLine::class)->orderBy('sort_order');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PpmpConsolidationVersion::class)->orderByDesc('version_number');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [
            PpmpConsolidationStatus::Draft,
            PpmpConsolidationStatus::ReturnedForRevision,
        ], true);
    }

    public function isLocked(): bool
    {
        return $this->status === PpmpConsolidationStatus::Locked;
    }

    public function isCancelled(): bool
    {
        return $this->status === PpmpConsolidationStatus::Cancelled;
    }

    public function recalculateTotal(): void
    {
        $total = $this->items()->get()->sum(fn (PpmpConsolidationItem $item) => $item->lineAbc());

        $this->update(['total_budget' => round((float) $total, 2)]);
    }
}
