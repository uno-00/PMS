<?php

namespace App\Models\Planning;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpmpConsolidationWfpLine extends Model
{
    use HasUuid;

    protected $fillable = [
        'ppmp_consolidation_id', 'activity', 'responsible_office', 'expected_output',
        'funding_source', 'budget_allocation', 'q1_budget', 'q2_budget', 'q3_budget', 'q4_budget',
        'schedule_start', 'schedule_end', 'sort_order',
    ];

    protected $casts = [
        'budget_allocation' => 'decimal:2',
        'q1_budget' => 'decimal:2',
        'q2_budget' => 'decimal:2',
        'q3_budget' => 'decimal:2',
        'q4_budget' => 'decimal:2',
        'schedule_start' => 'date',
        'schedule_end' => 'date',
    ];

    public function consolidation(): BelongsTo
    {
        return $this->belongsTo(PpmpConsolidation::class, 'ppmp_consolidation_id');
    }

    public function quarterlyTotal(): float
    {
        return round(
            (float) $this->q1_budget + (float) $this->q2_budget + (float) $this->q3_budget + (float) $this->q4_budget,
            2
        );
    }
}
