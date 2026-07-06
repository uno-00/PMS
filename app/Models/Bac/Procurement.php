<?php

namespace App\Models\Bac;

use App\Enums\ProcurementCaseStatus;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\HasWorkflow;
use App\Models\Procurement\NoticeOfAward;
use App\Models\Procurement\NoticeToProceed;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Settings\ModeOfProcurement;
use App\Models\Supplier\BidDocumentOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Procurement extends Model
{
    use HasAuditLog, HasUuid, HasWorkflow;

    protected $table = 'procurements';

    protected $fillable = [
        'case_no', 'purchase_request_id', 'mode_of_procurement_id', 'title', 'abc',
        'status', 'bac_chairperson_id', 'created_by', 'remarks',
    ];

    protected $casts = [
        'abc' => 'decimal:2',
        'status' => ProcurementCaseStatus::class,
    ];

    protected function auditableAttributes(): array
    {
        return ['status', 'abc', 'title'];
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function modeOfProcurement(): BelongsTo
    {
        return $this->belongsTo(ModeOfProcurement::class);
    }

    public function bacChairperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bac_chairperson_id');
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(BacCalendarEvent::class);
    }

    public function philgepsPosting(): HasOne
    {
        return $this->hasOne(PhilgepsPosting::class);
    }

    public function bidSubmissions(): HasMany
    {
        return $this->hasMany(BidSubmission::class);
    }

    public function clarifications(): HasMany
    {
        return $this->hasMany(BidClarification::class);
    }

    public function bidDocumentOrders(): HasMany
    {
        return $this->hasMany(BidDocumentOrder::class);
    }

    public function bidOpening(): HasOne
    {
        return $this->hasOne(BidOpening::class);
    }

    public function postQualifications(): HasMany
    {
        return $this->hasMany(PostQualification::class);
    }

    public function noticeOfAward(): HasOne
    {
        return $this->hasOne(NoticeOfAward::class);
    }

    public function noticeToProceed(): HasOne
    {
        return $this->hasOne(NoticeToProceed::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function requiresBidding(): bool
    {
        return (bool) ($this->modeOfProcurement?->requires_bac ?? true);
    }
}
