<?php

namespace App\Models\Settings;

use App\Enums\FiscalYearStatus;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use App\Models\Planning\AnnualProcurementPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FiscalYear extends Model
{
    use HasAuditLog, HasFactory, HasUuid;

    protected $fillable = [
        'year', 'start_date', 'end_date', 'status', 'is_current', 'total_gaa_amount', 'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => FiscalYearStatus::class,
        'is_current' => 'boolean',
        'total_gaa_amount' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function gaa(): HasOne
    {
        return $this->hasOne(GeneralAppropriationsAct::class);
    }

    public function annualProcurementPlan(): HasOne
    {
        return $this->hasOne(AnnualProcurementPlan::class);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public static function currentOrFail(): self
    {
        return static::current()->firstOrFail();
    }
}
