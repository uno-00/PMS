<?php

namespace App\Models\Planning;

use App\Enums\PpmpExpenseClass;
use App\Enums\PpmpProjectType;
use App\Enums\PreProcurementConference;
use App\Models\Concerns\HasUuid;
use App\Models\Settings\Division;
use App\Models\Settings\FundSource;
use App\Models\Settings\ModeOfProcurement;
use App\Models\Settings\Pap;
use App\Models\Settings\UacsCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpmpConsolidationItem extends Model
{
    use HasUuid;

    protected $fillable = [
        'ppmp_consolidation_id', 'source_ppmp_id', 'division_id', 'group_key', 'source_ppmp_item_ids',
        'item_no', 'expense_class', 'project_type', 'item_name', 'description', 'specification',
        'unit', 'quantity', 'estimated_unit_cost', 'line_abc', 'mode_of_procurement_id',
        'fund_source_id', 'pap_id', 'uacs_code_id', 'schedule_start', 'schedule_end',
        'pre_procurement_conference', 'remarks', 'is_merged', 'sort_order',
    ];

    protected $casts = [
        'source_ppmp_item_ids' => 'array',
        'quantity' => 'decimal:2',
        'estimated_unit_cost' => 'decimal:2',
        'line_abc' => 'decimal:2',
        'schedule_start' => 'date',
        'schedule_end' => 'date',
        'expense_class' => PpmpExpenseClass::class,
        'project_type' => PpmpProjectType::class,
        'pre_procurement_conference' => PreProcurementConference::class,
        'is_merged' => 'boolean',
    ];

    public function consolidation(): BelongsTo
    {
        return $this->belongsTo(PpmpConsolidation::class, 'ppmp_consolidation_id');
    }

    public function sourcePpmp(): BelongsTo
    {
        return $this->belongsTo(Ppmp::class, 'source_ppmp_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function modeOfProcurement(): BelongsTo
    {
        return $this->belongsTo(ModeOfProcurement::class);
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class);
    }

    public function pap(): BelongsTo
    {
        return $this->belongsTo(Pap::class);
    }

    public function uacsCode(): BelongsTo
    {
        return $this->belongsTo(UacsCode::class, 'uacs_code_id');
    }

    public function lineAbc(): float
    {
        return round((float) $this->quantity * (float) $this->estimated_unit_cost, 2);
    }
}
