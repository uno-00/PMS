<?php

namespace App\Models\Procurement;

use App\Enums\CafStatus;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HasWorkflow;
use App\Models\Settings\FundSource;
use App\Models\Settings\UacsCode;
use App\Models\User;
use App\Support\DocumentVerification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CertificateOfAvailabilityOfFunds extends Model
{
    use HasAuditLog, HasDocuments, HasUuid, HasWorkflow;

    protected $table = 'certificate_of_availability_of_funds';

    protected $fillable = [
        'caf_no', 'purchase_request_id', 'fund_source_id', 'uacs_code_id', 'amount', 'remaining_budget',
        'status', 'verification_code', 'generated_by', 'certified_by', 'certified_at',
        'approved_by', 'approved_at', 'printed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'remaining_budget' => 'decimal:2',
        'status' => CafStatus::class,
        'certified_at' => 'datetime',
        'approved_at' => 'datetime',
        'printed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $caf) {
            $caf->verification_code ??= (string) Str::uuid();
        });
    }

    protected function auditableAttributes(): array
    {
        return ['status', 'amount', 'remaining_budget'];
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class);
    }

    public function uacsCode(): BelongsTo
    {
        return $this->belongsTo(UacsCode::class, 'uacs_code_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function certifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certified_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function verificationUrl(): string
    {
        return DocumentVerification::urlFor('caf', $this->verification_code);
    }
}
