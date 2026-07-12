<?php

namespace App\Models\Planning;

use App\Enums\ProjectProposalPipelineStep;
use App\Enums\ProjectProposalStatus;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProjectProposal extends Model
{
    use HasAuditLog, HasUuid, SoftDeletes;

    protected $fillable = [
        'market_scoping_id', 'fiscal_year_id', 'division_id', 'document_ref', 'with_enclosures',
        'project_type', 'title', 'schedule', 'venue_area', 'total_cost', 'fund_source_text',
        'proponent', 'rationale', 'objectives', 'target_schedule', 'budgetary_requirement',
        'fund_source_narrative', 'status', 'pipeline_step', 'ppmp_id', 'prepared_by', 'submitted_at',
        'recommended_by', 'recommended_at', 'approved_by', 'approved_at', 'remarks',
    ];

    protected $casts = [
        'total_cost' => 'decimal:2',
        'status' => ProjectProposalStatus::class,
        'pipeline_step' => ProjectProposalPipelineStep::class,
        'submitted_at' => 'datetime',
        'recommended_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $record) {
            $record->control_no ??= 'PP-'.($record->fiscalYear?->year ?? now()->year).'-'.strtoupper(Str::random(6));
            $record->document_ref ??= 'NMP-PP-01';
        });
    }

    protected function auditableAttributes(): array
    {
        return ['title', 'status', 'total_cost', 'project_type'];
    }

    public function marketScoping(): BelongsTo
    {
        return $this->belongsTo(MarketScoping::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function ppmp(): BelongsTo
    {
        return $this->belongsTo(Ppmp::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function recommendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recommended_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [ProjectProposalStatus::Draft, ProjectProposalStatus::ReturnedForRevision], true);
    }

    public function hasGeneratedPpmp(): bool
    {
        return $this->ppmp_id !== null;
    }

    public function isPipelineComplete(): bool
    {
        return ($this->pipeline_step ?? ProjectProposalPipelineStep::ProjectProposal) === ProjectProposalPipelineStep::Completed;
    }
}
