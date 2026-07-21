<?php

namespace App\Models\Supplier;

use App\Models\Bac\BidClarification;
use App\Models\Bac\BidSubmission;
use App\Models\Bac\PostQualification;
use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasUuid;
use App\Models\Procurement\NoticeOfAward;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Bidder extends Model
{
    use HasAuditLog, HasDocuments, HasFactory, HasUuid, Notifiable;

    protected $fillable = [
        'user_id', 'company_name', 'business_type', 'philgeps_registration_no', 'philgeps_registration_expiry',
        'mayor_permit_no', 'mayor_permit_expiry', 'tax_clearance_no', 'tax_clearance_expiry',
        'sec_dti_registration_no', 'pcab_license_no', 'pcab_license_expiry', 'tin', 'contact_person',
        'email', 'phone', 'address', 'status', 'remarks',
    ];

    protected $casts = [
        'philgeps_registration_expiry' => 'date',
        'mayor_permit_expiry' => 'date',
        'tax_clearance_expiry' => 'date',
        'pcab_license_expiry' => 'date',
    ];

    public const DOCUMENT_CATEGORIES = [
        'philgeps_registration' => 'PhilGEPS Registration',
        'mayor_permit' => "Mayor's Permit",
        'tax_clearance' => 'Tax Clearance',
        'sec_dti_registration' => 'SEC / DTI Registration',
        'pcab_license' => 'PCAB License',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bidDocumentOrders(): HasMany
    {
        return $this->hasMany(BidDocumentOrder::class);
    }

    public function bidSubmissions(): HasMany
    {
        return $this->hasMany(BidSubmission::class);
    }

    public function clarifications(): HasMany
    {
        return $this->hasMany(BidClarification::class);
    }

    public function postQualifications(): HasMany
    {
        return $this->hasMany(PostQualification::class);
    }

    public function noticeOfAwards(): HasMany
    {
        return $this->hasMany(NoticeOfAward::class);
    }

    public function isEligible(): bool
    {
        return $this->status === 'verified';
    }

    public function routeNotificationForMail(): ?string
    {
        return $this->email;
    }
}
