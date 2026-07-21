<?php

namespace App\Models\Settings;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Office extends Model
{
    use HasAuditLog, HasUuid;

    protected $fillable = ['division_id', 'code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
}
