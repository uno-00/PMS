<?php

namespace App\Services\Procurement;

use App\Enums\CafStatus;
use App\Models\Procurement\CertificateOfAvailabilityOfFunds;
use App\Models\Procurement\PurchaseRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CafService
{
    /**
     * Phase 6: automatically generates the Certificate of Availability of
     * Funds as soon as a Purchase Request clears HOPE approval.
     */
    public function generate(PurchaseRequest $pr, ?User $user = null): CertificateOfAvailabilityOfFunds
    {
        if ($pr->certificateOfAvailabilityOfFunds) {
            return $pr->certificateOfAvailabilityOfFunds;
        }

        $primaryItem = $pr->items()->with('ppmpItem')->first();
        $ppmpItem = $primaryItem?->ppmpItem;
        $allocation = $ppmpItem?->budgetAllocation;
        $amount = (float) ($pr->total_amount ?? $pr->items()->sum('amount'));

        return DB::transaction(fn () => CertificateOfAvailabilityOfFunds::query()->create([
            'caf_no' => 'CAF-'.$pr->fiscalYear->year.'-'.str_pad((string) (CertificateOfAvailabilityOfFunds::query()->count() + 1), 5, '0', STR_PAD_LEFT),
            'purchase_request_id' => $pr->id,
            'fund_source_id' => $ppmpItem?->fund_source_id,
            'uacs_code_id' => $ppmpItem?->uacs_code_id,
            'amount' => $amount,
            'remaining_budget' => $allocation?->remaining_balance ?? 0,
            'status' => CafStatus::Generated,
            'generated_by' => $user?->id,
        ]));
    }

    public function certify(CertificateOfAvailabilityOfFunds $caf, User $user, ?string $remarks = null): CertificateOfAvailabilityOfFunds
    {
        $caf->update(['certified_by' => $user->id, 'certified_at' => now()]);
        $caf->transitionTo(CafStatus::Certified, $remarks, action: 'certified');

        return $caf->fresh();
    }

    public function approve(CertificateOfAvailabilityOfFunds $caf, User $user, ?string $remarks = null): CertificateOfAvailabilityOfFunds
    {
        $caf->update(['approved_by' => $user->id, 'approved_at' => now()]);
        $caf->transitionTo(CafStatus::Approved, $remarks, action: 'approved');

        return $caf->fresh();
    }

    public function markPrinted(CertificateOfAvailabilityOfFunds $caf): CertificateOfAvailabilityOfFunds
    {
        $caf->update(['printed_at' => now()]);
        $caf->transitionTo(CafStatus::Printed, 'CAF printed.', action: 'printed');

        return $caf->fresh();
    }
}
