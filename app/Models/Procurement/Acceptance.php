<?php

namespace App\Models\Procurement;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acceptance extends Model
{
    use HasAuditLog, HasDocuments, HasUuid;

    protected $fillable = ['delivery_id', 'accepted_by', 'accepted_date', 'remarks'];

    protected $casts = ['accepted_date' => 'date'];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }
}
