<?php

namespace App\Models\Procurement;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inspection extends Model
{
    use HasAuditLog, HasDocuments, HasUuid;

    protected $fillable = ['delivery_id', 'inspected_by', 'inspection_date', 'result', 'remarks'];

    protected $casts = ['inspection_date' => 'date'];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }
}
