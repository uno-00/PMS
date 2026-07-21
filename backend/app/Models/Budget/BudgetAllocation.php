<?php

namespace App\Models\Budget;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use App\Models\Settings\CostCenter;
use App\Models\Settings\Department;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Models\Settings\FundSource;
use App\Models\Settings\Office;
use App\Models\Settings\Pap;
use App\Models\Settings\UacsCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetAllocation extends Model
{
    use HasAuditLog, HasFactory, HasUuid;

    protected $fillable = [
        'fiscal_year_id', 'parent_id', 'gaa_id', 'level', 'department_id', 'division_id',
        'office_id', 'cost_center_id', 'pap_id', 'fund_source_id', 'uacs_code_id',
        'allocated_amount', 'utilized_amount', 'remarks', 'created_by',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'utilized_amount' => 'decimal:2',
    ];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function gaa(): BelongsTo
    {
        return $this->belongsTo(GeneralAppropriationsAct::class, 'gaa_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function pap(): BelongsTo
    {
        return $this->belongsTo(Pap::class);
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class);
    }

    public function uacsCode(): BelongsTo
    {
        return $this->belongsTo(UacsCode::class, 'uacs_code_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Funds still free to sub-allocate or utilize at this node: what was
     * allocated here, minus what has already been utilized directly and
     * minus whatever has already been pushed down to child allocations.
     */
    public function getRemainingBalanceAttribute(): float
    {
        $childAllocated = (float) $this->children()->sum('allocated_amount');

        return round((float) $this->allocated_amount - (float) $this->utilized_amount - $childAllocated, 2);
    }

    public function isLeaf(): bool
    {
        return ! $this->children()->exists();
    }
}
