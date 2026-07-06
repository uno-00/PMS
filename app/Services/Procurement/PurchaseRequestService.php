<?php

namespace App\Services\Procurement;

use App\Enums\PurchaseRequestStatus;
use App\Events\Procurement\PurchaseRequestApproved;
use App\Exceptions\BudgetExceededException;
use App\Models\Planning\PpmpItem;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\PurchaseRequestItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Phase 5 rules: a Purchase Request may only draw from an Approved/Locked
 * PPMP, every peso requested is checked against that PPMP item's real-time
 * remaining balance (accounting for every other in-flight PR against the
 * same item so two requesters can never double-spend the same funds), and
 * the balance is only permanently committed once the PR clears HOPE.
 */
class PurchaseRequestService
{
    /**
     * Funds still free to request against a PPMP item: its ABC, minus
     * amounts already committed by approved PRs, minus amounts reserved
     * by any other PR currently in-flight (not rejected/cancelled).
     */
    public function availableForPpmpItem(PpmpItem $item, ?string $excludePrId = null): float
    {
        $reserved = PurchaseRequestItem::query()
            ->where('ppmp_item_id', $item->id)
            ->whereHas('purchaseRequest', function ($q) use ($excludePrId) {
                $q->whereNotIn('status', [PurchaseRequestStatus::Rejected->value, PurchaseRequestStatus::Cancelled->value]);
                if ($excludePrId) {
                    $q->where('id', '!=', $excludePrId);
                }
            })
            ->sum('amount');

        return round((float) $item->abc - (float) $item->utilized_amount - (float) $reserved, 2);
    }

    public function addItem(PurchaseRequest $pr, PpmpItem $ppmpItem, float $quantity, float $unitCost): PurchaseRequestItem
    {
        abort_unless($pr->isEditable(), 422, 'This Purchase Request can no longer be edited.');

        $amount = round($quantity * $unitCost, 2);
        $available = $this->availableForPpmpItem($ppmpItem, $pr->id);

        if ($amount > $available) {
            throw BudgetExceededException::forAllocation($amount, $available);
        }

        return $pr->items()->create([
            'ppmp_item_id' => $ppmpItem->id,
            'item_name' => $ppmpItem->item_name,
            'description' => $ppmpItem->description,
            'unit' => $ppmpItem->unit,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'amount' => $amount,
        ]);
    }

    public function submit(PurchaseRequest $pr, User $user): PurchaseRequest
    {
        abort_if($pr->items()->count() === 0, 422, 'Add at least one item before submitting.');
        $pr->update(['requested_by' => $pr->requested_by ?? $user->id, 'submitted_at' => now()]);
        $pr->transitionTo(PurchaseRequestStatus::DivisionChief, 'Submitted for Division Chief review.', action: 'submitted');

        return $pr->fresh();
    }

    public function divisionChiefApprove(PurchaseRequest $pr, User $user, ?string $remarks = null): PurchaseRequest
    {
        $pr->update(['division_chief_by' => $user->id, 'division_chief_at' => now()]);
        $pr->transitionTo(PurchaseRequestStatus::Planning, $remarks, action: 'division-chief-approved');

        return $pr->fresh();
    }

    public function planningApprove(PurchaseRequest $pr, User $user, ?string $remarks = null): PurchaseRequest
    {
        $pr->update(['planning_by' => $user->id, 'planning_at' => now()]);
        $pr->transitionTo(PurchaseRequestStatus::Budget, $remarks, action: 'planning-approved');

        return $pr->fresh();
    }

    public function budgetApprove(PurchaseRequest $pr, User $user, ?string $remarks = null): PurchaseRequest
    {
        $pr->update(['budget_by' => $user->id, 'budget_at' => now()]);
        $pr->transitionTo(PurchaseRequestStatus::Hope, $remarks, action: 'budget-approved');

        return $pr->fresh();
    }

    /**
     * Final HOPE approval: permanently commits utilization against every
     * referenced PPMP item and fires PurchaseRequestApproved, which
     * automatically generates the CAF (Phase 6).
     */
    public function hopeApprove(PurchaseRequest $pr, User $user, ?string $remarks = null): PurchaseRequest
    {
        return DB::transaction(function () use ($pr, $user, $remarks) {
            foreach ($pr->items as $item) {
                $ppmpItem = $item->ppmpItem()->lockForUpdate()->first();
                $available = $this->availableForPpmpItem($ppmpItem, $pr->id) + (float) $item->amount;
                // re-validate right before committing in case sibling PRs changed the balance
                if ((float) $item->amount > $this->availableForPpmpItem($ppmpItem, $pr->id)) {
                    throw BudgetExceededException::forAllocation((float) $item->amount, $available);
                }
                $ppmpItem->increment('utilized_amount', $item->amount);
            }

            $pr->update(['hope_by' => $user->id, 'hope_at' => now()]);
            $pr->transitionTo(PurchaseRequestStatus::Approved, $remarks, action: 'approved');

            PurchaseRequestApproved::dispatch($pr->fresh());

            return $pr->fresh();
        });
    }

    public function reject(PurchaseRequest $pr, User $user, string $remarks): PurchaseRequest
    {
        $pr->transitionTo(PurchaseRequestStatus::Rejected, $remarks, action: 'rejected', enforce: false);

        return $pr->fresh();
    }

    public function cancel(PurchaseRequest $pr, User $user, string $remarks): PurchaseRequest
    {
        return DB::transaction(function () use ($pr, $remarks) {
            if ($pr->status === PurchaseRequestStatus::Approved) {
                foreach ($pr->items as $item) {
                    $item->ppmpItem?->decrement('utilized_amount', min((float) $item->amount, (float) $item->ppmpItem->utilized_amount));
                }
            }

            $pr->transitionTo(PurchaseRequestStatus::Cancelled, $remarks, action: 'cancelled', enforce: false);

            return $pr->fresh();
        });
    }
}
