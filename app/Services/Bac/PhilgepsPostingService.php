<?php

namespace App\Services\Bac;

use App\Enums\PhilgepsPostingStatus;
use App\Enums\ProcurementCaseStatus;
use App\Events\Bac\PhilgepsPostingPublished;
use App\Models\Bac\PhilgepsPosting;
use App\Models\Bac\Procurement;
use App\Models\User;

/**
 * Phase 8. Supports either a live PhilGEPS API push (when configured) or
 * manual posting entry (reference number typed in after posting directly
 * on the PhilGEPS website), per the "support manual upload if API
 * unavailable" requirement.
 */
class PhilgepsPostingService
{
    public function post(Procurement $procurement, array $attributes, User $user): PhilgepsPosting
    {
        $manualMode = (bool) config('services.philgeps.manual_mode', true);

        $posting = PhilgepsPosting::query()->create([
            'procurement_id' => $procurement->id,
            'reference_no' => $attributes['reference_no'] ?? $this->pushToPhilgepsApi($procurement),
            'posting_date' => $attributes['posting_date'] ?? now(),
            'closing_date' => $attributes['closing_date'],
            'status' => PhilgepsPostingStatus::Published,
            'is_manual' => $manualMode,
            'remarks' => $attributes['remarks'] ?? null,
            'posted_by' => $user->id,
        ]);

        $procurement->transitionTo(ProcurementCaseStatus::Posted, 'Posted to PhilGEPS.');

        PhilgepsPostingPublished::dispatch($posting);

        return $posting;
    }

    /**
     * Placeholder integration point for the live PhilGEPS API. Returns
     * null when unavailable so the caller falls back to manual entry.
     */
    protected function pushToPhilgepsApi(Procurement $procurement): ?string
    {
        if (blank(config('services.philgeps.base_url'))) {
            return null;
        }

        // Real integration would POST the procurement details to PhilGEPS
        // here and return the reference number issued back.
        return null;
    }

    public function close(PhilgepsPosting $posting): PhilgepsPosting
    {
        $posting->transitionTo(PhilgepsPostingStatus::Closed, 'Posting closed.');
        $posting->procurement->transitionTo(ProcurementCaseStatus::Bidding, 'Bid submission window closed; proceeding to bidding.');

        return $posting->fresh();
    }

    public function cancel(PhilgepsPosting $posting, string $remarks): PhilgepsPosting
    {
        $posting->transitionTo(PhilgepsPostingStatus::Cancelled, $remarks);

        return $posting->fresh();
    }
}
