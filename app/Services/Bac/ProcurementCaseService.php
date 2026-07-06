<?php

namespace App\Services\Bac;

use App\Enums\ProcurementCaseStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Bac\Procurement;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Settings\ModeOfProcurement;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Phase 7 entry point: BAC Secretariat picks up an Approved Purchase
 * Request and opens a procurement case, auto-resolving the applicable
 * mode of procurement from the configurable threshold table (never
 * hardcoded) so the rest of the bidding pipeline knows whether full
 * competitive bidding is required.
 */
class ProcurementCaseService
{
    public function openCase(PurchaseRequest $pr, User $user, string $category = 'goods', ?string $title = null): Procurement
    {
        abort_unless($pr->status === PurchaseRequestStatus::Approved, 422, 'Only an approved Purchase Request can be opened as a procurement case.');

        $mode = ModeOfProcurement::resolveForAmount((float) $pr->total_amount, $category);

        return Procurement::query()->firstOrCreate(
            ['purchase_request_id' => $pr->id],
            [
                'case_no' => 'PR-CASE-'.now()->format('Y').'-'.strtoupper(Str::random(6)),
                'mode_of_procurement_id' => $mode?->id,
                'title' => $title ?? $pr->purpose,
                'abc' => $pr->total_amount,
                'status' => ProcurementCaseStatus::Planning,
                'created_by' => $user->id,
            ]
        );
    }

    public function moveTo(Procurement $procurement, ProcurementCaseStatus $status, ?string $remarks = null): Procurement
    {
        $procurement->transitionTo($status, $remarks);

        return $procurement->fresh();
    }

    public function cancel(Procurement $procurement, string $remarks): Procurement
    {
        $procurement->transitionTo(ProcurementCaseStatus::Cancelled, $remarks, enforce: false);

        return $procurement->fresh();
    }
}
