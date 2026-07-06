<?php

namespace App\Models\Settings;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasAuditLog, HasUuid;

    protected $fillable = ['code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }
}
