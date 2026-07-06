<?php

namespace App\Models\Settings;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModeOfProcurement extends Model
{
    use HasAuditLog, HasUuid;

    protected $table = 'modes_of_procurement';

    protected $fillable = [
        'code', 'name', 'description', 'requires_bac', 'requires_philgeps_posting', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'requires_bac' => 'boolean',
        'requires_philgeps_posting' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function thresholds(): HasMany
    {
        return $this->hasMany(ProcurementThreshold::class);
    }

    /**
     * Resolve the applicable mode of procurement for a given ABC amount and
     * category based on the configured threshold table, instead of a
     * hardcoded if/else chain. Falls back to Public Bidding when no
     * threshold matches (the RA 12009 default mode).
     */
    public static function resolveForAmount(float $amount, string $category = 'goods'): ?self
    {
        $threshold = ProcurementThreshold::query()
            ->where('category', $category)
            ->where('is_active', true)
            ->where('min_amount', '<=', $amount)
            ->where(function ($q) use ($amount) {
                $q->whereNull('max_amount')->orWhere('max_amount', '>=', $amount);
            })
            ->orderByDesc('min_amount')
            ->first();

        return $threshold?->modeOfProcurement ?? static::query()->where('code', 'PB')->first();
    }
}
