<?php

namespace App\Models\Planning;

use App\Enums\MarketScopingStatus;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MarketScoping extends Model
{
    use HasAuditLog, HasUuid, SoftDeletes;

    protected $fillable = [
        'project_proposal_id', 'fiscal_year_id', 'division_id', 'procuring_entity', 'end_user_unit',
        'representative_name', 'representative_designation', 'project_name',
        'estimated_budget', 'period_from', 'period_to', 'expected_delivery',
        'activities', 'parameters', 'status', 'prepared_by', 'approved_by',
        'approved_at', 'remarks',
    ];

    protected $casts = [
        'estimated_budget' => 'decimal:2',
        'period_from' => 'date',
        'period_to' => 'date',
        'expected_delivery' => 'date',
        'activities' => 'array',
        'parameters' => 'array',
        'status' => MarketScopingStatus::class,
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $record) {
            $record->control_no ??= 'MSC-'.($record->fiscalYear?->year ?? now()->year).'-'.strtoupper(Str::random(6));
        });
    }

    protected function auditableAttributes(): array
    {
        return ['project_name', 'status', 'estimated_budget', 'end_user_unit'];
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function projectProposal(): BelongsTo
    {
        return $this->belongsTo(ProjectProposal::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function projectProposals(): HasMany
    {
        return $this->hasMany(ProjectProposal::class);
    }

    public function isEditable(): bool
    {
        return $this->status === MarketScopingStatus::Draft;
    }

    public function isApproved(): bool
    {
        return $this->status === MarketScopingStatus::Approved;
    }
}
