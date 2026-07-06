<?php

namespace App\Models\Settings;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasAuditLog, HasUuid;

    protected $fillable = ['date', 'name', 'type', 'is_active'];

    protected $casts = ['date' => 'date', 'is_active' => 'boolean'];
}
