<?php

namespace App\Models\Planning;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpmpConsolidationSource extends Model
{
    use HasUuid;

    protected $fillable = ['ppmp_consolidation_id', 'ppmp_id'];

    public function consolidation(): BelongsTo
    {
        return $this->belongsTo(PpmpConsolidation::class, 'ppmp_consolidation_id');
    }

    public function ppmp(): BelongsTo
    {
        return $this->belongsTo(Ppmp::class);
    }
}
