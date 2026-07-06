<?php

namespace App\Models\Settings;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasAuditLog, HasUuid;

    protected $fillable = ['department_id', 'code', 'name', 'chief_user_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function chief(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chief_user_id');
    }

    public function offices(): HasMany
    {
        return $this->hasMany(Office::class);
    }

    public function costCenters(): HasMany
    {
        return $this->hasMany(CostCenter::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
