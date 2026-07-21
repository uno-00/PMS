<?php

namespace App\Models\Bac;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\Supplier\Bidder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostQualification extends Model
{
    use HasAuditLog, HasDocuments, HasUuid;

    protected $fillable = [
        'procurement_id', 'bidder_id', 'site_visit_conducted', 'document_validation_notes',
        'result', 'processed_by', 'processed_at',
    ];

    protected $casts = ['site_visit_conducted' => 'boolean', 'processed_at' => 'datetime'];

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    public function bidder(): BelongsTo
    {
        return $this->belongsTo(Bidder::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
