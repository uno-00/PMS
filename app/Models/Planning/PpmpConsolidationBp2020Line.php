<?php

namespace App\Models\Planning;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpmpConsolidationBp2020Line extends Model
{
    use HasUuid;

    protected $fillable = [
        'ppmp_consolidation_id', 'ppmp_consolidation_item_id', 'program', 'activity', 'project',
        'procurement_item', 'quantity', 'unit', 'unit_cost', 'annual_requirement',
        'budget_allocation', 'fund_source', 'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'annual_requirement' => 'decimal:2',
        'budget_allocation' => 'decimal:2',
    ];

    public function consolidation(): BelongsTo
    {
        return $this->belongsTo(PpmpConsolidation::class, 'ppmp_consolidation_id');
    }

    public function consolidationItem(): BelongsTo
    {
        return $this->belongsTo(PpmpConsolidationItem::class, 'ppmp_consolidation_item_id');
    }
}
