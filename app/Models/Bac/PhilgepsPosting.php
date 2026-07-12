<?php

namespace App\Models\Bac;

use App\Enums\PhilgepsPostingStatus;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HasWorkflow;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhilgepsPosting extends Model
{
    use HasAuditLog, HasDocuments, HasFactory, HasUuid, HasWorkflow;

    protected $table = 'philgeps_postings';

    protected $fillable = [
        'procurement_id', 'reference_no', 'posting_date', 'closing_date', 'status', 'is_manual', 'remarks', 'posted_by',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'closing_date' => 'date',
        'status' => PhilgepsPostingStatus::class,
        'is_manual' => 'boolean',
    ];

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function isOpenForSubmission(): bool
    {
        return $this->status === PhilgepsPostingStatus::Published && now()->lte($this->closing_date->endOfDay());
    }
}
