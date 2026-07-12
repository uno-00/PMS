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

    /**
     * Manually record a PhilGEPS posting for a procurement that was posted
     * directly on the PhilGEPS website (reference number typed in). Distinct
     * from post() which assumes an automated/API flow; this always marks the
     * record as manual. The procurement transitions to Posted.
     */
    public function createManual(Procurement $procurement, array $attributes, User $user): PhilgepsPosting
    {
        $posting = PhilgepsPosting::query()->create([
            'procurement_id' => $procurement->id,
            'reference_no' => $attributes['reference_no'] ?? null,
            'posting_date' => $attributes['posting_date'],
            'closing_date' => $attributes['closing_date'],
            'status' => PhilgepsPostingStatus::Published,
            'is_manual' => true,
            'remarks' => $attributes['remarks'] ?? null,
            'posted_by' => $user->id,
        ]);

        $procurement->transitionTo(ProcurementCaseStatus::Posted, 'Manually recorded PhilGEPS posting.');

        PhilgepsPostingPublished::dispatch($posting);

        return $posting;
    }

    /**
     * Edit an existing manual posting's reference number, dates, and remarks.
     * Only manual postings still in the Published state are editable (the
     * policy enforces this); this method trusts the caller's authorization.
     */
    public function update(PhilgepsPosting $posting, array $attributes): PhilgepsPosting
    {
        $posting->update([
            'reference_no' => $attributes['reference_no'] ?? $posting->reference_no,
            'posting_date' => $attributes['posting_date'] ?? $posting->posting_date,
            'closing_date' => $attributes['closing_date'] ?? $posting->closing_date,
            'remarks' => array_key_exists('remarks', $attributes) ? ($attributes['remarks'] ?: null) : $posting->remarks,
        ]);

        return $posting->fresh();
    }

    /**
     * Remove a manual, still-Published posting. API-originated or
     * Closed/Cancelled postings are read-only (enforced by policy). Deleting
     * a posting does not reverse the procurement's Posted transition, since
     * the case may already have advanced through the workflow.
     */
    public function delete(PhilgepsPosting $posting): void
    {
        $posting->delete();
    }
}
