<?php

namespace App\Models\Settings;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementThreshold extends Model
{
    use HasAuditLog, HasUuid;

    protected $fillable = [
        'mode_of_procurement_id', 'category', 'min_amount', 'max_amount', 'effective_date', 'is_active',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'effective_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function modeOfProcurement(): BelongsTo
    {
        return $this->belongsTo(ModeOfProcurement::class);
    }
}
