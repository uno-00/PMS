<?php

namespace App\Models\Bac;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BidOpening extends Model
{
    use HasAuditLog, HasDocuments, HasUuid;

    protected $fillable = ['procurement_id', 'opened_at', 'opened_by', 'checklist', 'attendance'];

    protected $casts = ['opened_at' => 'datetime', 'checklist' => 'array', 'attendance' => 'array'];

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }
}
