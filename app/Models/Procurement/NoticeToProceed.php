<?php

namespace App\Models\Procurement;

use App\Models\Bac\Procurement;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NoticeToProceed extends Model
{
    use HasAuditLog, HasDocuments, HasUuid;

    protected $table = 'notice_to_proceeds';

    protected $fillable = [
        'ntp_no', 'procurement_id', 'effectivity_date', 'contract_duration_days',
        'completion_date', 'issued_at', 'status', 'issued_by',
    ];

    protected $casts = [
        'effectivity_date' => 'date',
        'completion_date' => 'date',
        'issued_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $ntp) {
            $ntp->ntp_no ??= 'NTP-'.now()->format('Y').'-'.strtoupper(Str::random(6));

            if ($ntp->effectivity_date && $ntp->contract_duration_days && ! $ntp->completion_date) {
                $ntp->completion_date = Carbon::parse($ntp->effectivity_date)
                    ->addDays($ntp->contract_duration_days);
            }
        });
    }

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
