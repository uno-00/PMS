<?php

namespace App\Models\Planning;

use App\Enums\PreProcurementConference;

use App\Models\Budget\BudgetAllocation;
use App\Models\Concerns\HasUuid;
use App\Models\Settings\FundSource;
use App\Models\Settings\ModeOfProcurement;
use App\Models\Settings\Pap;
use App\Models\Settings\UacsCode;
use App\Support\RichTextSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpmpItem extends Model
{
    use HasUuid;

    protected $fillable = [
        'ppmp_id', 'item_no', 'item_name', 'description', 'specification', 'unit', 'quantity',
        'estimated_unit_cost', 'abc', 'schedule_start', 'schedule_end', 'mode_of_procurement_id',
        'pre_procurement_conference', 'fund_source_id', 'pap_id', 'uacs_code_id', 'budget_allocation_id', 'utilized_amount', 'remarks',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'estimated_unit_cost' => 'decimal:2',
        'abc' => 'decimal:2',
        'utilized_amount' => 'decimal:2',
        'schedule_start' => 'date',
        'schedule_end' => 'date',
        'pre_procurement_conference' => PreProcurementConference::class,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            if ($item->quantity && $item->estimated_unit_cost) {
                $item->abc = round((float) $item->quantity * (float) $item->estimated_unit_cost, 2);
            }
        });

        static::saved(fn (self $item) => $item->ppmp?->recalculateTotal());
        static::deleted(fn (self $item) => $item->ppmp?->recalculateTotal());
    }

    public function ppmp(): BelongsTo
    {
        return $this->belongsTo(Ppmp::class);
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

    public function budgetAllocation(): BelongsTo
    {
        return $this->belongsTo(BudgetAllocation::class);
    }

    public function remainingBalance(): float
    {
        return round((float) $this->abc - (float) $this->utilized_amount, 2);
    }

    public function plainDescription(): string
    {
        return RichTextSanitizer::plainText($this->description);
    }

    public function plainSpecification(): string
    {
        return RichTextSanitizer::plainText($this->specification);
    }

    public function plainRemarks(): string
    {
        return RichTextSanitizer::plainText($this->remarks);
    }
}
