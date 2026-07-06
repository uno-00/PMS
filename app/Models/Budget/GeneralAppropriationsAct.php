<?php

namespace App\Models\Budget;

use App\Enums\GaaStatus;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HasWorkflow;
use App\Models\Settings\FiscalYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class GeneralAppropriationsAct extends Model
{
    use HasAuditLog, HasUuid, HasWorkflow;

    protected $table = 'general_appropriations_acts';

    protected $fillable = [
        'fiscal_year_id', 'reference_no', 'title', 'file_disk', 'file_path', 'original_filename',
        'total_amount', 'status', 'validation_errors', 'uploaded_by', 'validated_by', 'validated_at',
        'approved_by', 'approved_at', 'distributed_by', 'distributed_at', 'remarks',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'status' => GaaStatus::class,
        'validation_errors' => 'array',
        'validated_at' => 'datetime',
        'approved_at' => 'datetime',
        'distributed_at' => 'datetime',
    ];

    protected function auditableAttributes(): array
    {
        return ['title', 'status', 'total_amount', 'reference_no'];
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(GaaLineItem::class, 'gaa_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }

    public function summaryByPap(): Collection
    {
        return $this->lineItems()
            ->selectRaw('pap_id, sum(amount) as total')
            ->groupBy('pap_id')
            ->with('pap')
            ->get();
    }

    public function summaryByDepartment(): Collection
    {
        return $this->lineItems()
            ->selectRaw('department_id, sum(amount) as total')
            ->groupBy('department_id')
            ->with('department')
            ->get();
    }
}
