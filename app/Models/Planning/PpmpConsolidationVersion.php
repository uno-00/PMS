<?php

namespace App\Models\Planning;

use App\Models\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpmpConsolidationVersion extends Model
{
    use HasUuid;

    protected $fillable = [
        'ppmp_consolidation_id', 'version_number', 'status_at_snapshot', 'snapshot', 'created_by',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'snapshot' => 'array',
    ];

    public function consolidation(): BelongsTo
    {
        return $this->belongsTo(PpmpConsolidation::class, 'ppmp_consolidation_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
