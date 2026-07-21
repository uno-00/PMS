<?php

namespace App\Models\Planning;

use App\Enums\AnnualProcurementPlanStatus;
use App\Enums\PpmpStatus;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HasWorkflow;
use App\Models\Settings\FiscalYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnualProcurementPlan extends Model
{
    use HasAuditLog, HasUuid, HasWorkflow;

    protected $table = 'annual_procurement_plans';

    protected $fillable = [
        'fiscal_year_id', 'gaa_id', 'reference_no', 'total_budget', 'total_planned_amount', 'status',
        'consolidated_by', 'consolidated_at', 'reviewed_by', 'reviewed_at',
        'approved_by', 'approved_at', 'locked_by', 'locked_at', 'remarks',
    ];

    protected $casts = [
        'total_budget' => 'decimal:2',
        'total_planned_amount' => 'decimal:2',
        'status' => AnnualProcurementPlanStatus::class,
        'consolidated_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    protected function auditableAttributes(): array
    {
        return ['status', 'total_budget', 'total_planned_amount', 'reference_no'];
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function gaa(): BelongsTo
    {
        return $this->belongsTo(GeneralAppropriationsAct::class, 'gaa_id');
    }

    public function ppmps(): HasMany
    {
        return $this->hasMany(Ppmp::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consolidated_by');
    }

    public function remainingBudget(): float
    {
        return round((float) $this->total_budget - (float) $this->total_planned_amount, 2);
    }

    public function isLocked(): bool
    {
        return $this->status === AnnualProcurementPlanStatus::Locked;
    }

    public function recalculatePlannedAmount(): void
    {
        $this->update([
            'total_planned_amount' => $this->ppmps()->whereIn('status', [
                PpmpStatus::Approved->value,
                PpmpStatus::Locked->value,
            ])->sum('total_abc'),
        ]);
    }
}
