<?php

namespace App\Models\Bac;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BidEvaluation extends Model
{
    use HasAuditLog, HasUuid;

    protected $fillable = ['bid_submission_id', 'evaluator_id', 'compliance', 'score', 'rank', 'remarks', 'recommendation'];

    protected $casts = ['compliance' => 'array', 'score' => 'decimal:2'];

    public function bidSubmission(): BelongsTo
    {
        return $this->belongsTo(BidSubmission::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
