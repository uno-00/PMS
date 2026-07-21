<?php

namespace App\Models\Settings;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Program / Activity / Project (PAP) classification, part of the UACS.
 */
class Pap extends Model
{
    use HasAuditLog, HasUuid;

    protected $fillable = ['parent_id', 'type', 'code', 'name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopePrograms($query)
    {
        return $query->where('type', 'program');
    }

    public function getFullCodeAttribute(): string
    {
        return $this->parent ? "{$this->parent->code}-{$this->code}" : $this->code;
    }
}
