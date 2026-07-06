<?php

namespace App\Models\Settings;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

class UacsCode extends Model
{
    use HasAuditLog, HasUuid;

    protected $table = 'uacs_codes';

    protected $fillable = ['code', 'description', 'expense_class', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
