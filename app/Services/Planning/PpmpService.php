<?php

namespace App\Services\Planning;

use App\Enums\PpmpStatus;
use App\Exceptions\BudgetExceededException;
use App\Models\Budget\BudgetAllocation;
use App\Models\Planning\Ppmp;
use App\Models\User;
use App\Services\Budget\BudgetAllocationService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Owns Phase 4 (PPMP) business rules: automatic totals, budget-ceiling
 * validation before submission, the multi-step approval routing (Division
 * Chief -> Planning -> Budget -> BAC Consolidation -> Approved -> Locked),
 * and Supplemental/Amended PPMP creation while preserving full revision
 * lineage back to the original plan.
 */
class PpmpService
{
    public function __construct(protected BudgetAllocationService $allocations) {}

    public function submitForReview(Ppmp $ppmp, User $user): Ppmp
    {
        abort_if($ppmp->items()->count() === 0, 422, 'Add at least one item before submitting the PPMP.');

        $ppmp->update(['prepared_by' => $ppmp->prepared_by ?? $user->id, 'submitted_at' => now()]);
        $ppmp->transitionTo(PpmpStatus::DivisionChiefReview, 'Submitted by preparer for Division Chief review.', action: 'submitted');

        return $ppmp->fresh();
    }

    public function divisionChiefApprove(Ppmp $ppmp, User $user, ?string $remarks = null): Ppmp
    {
        $ppmp->update(['division_chief_by' => $user->id, 'division_chief_at' => now()]);
        $ppmp->transitionTo(PpmpStatus::PlanningReview, $remarks, action: 'division-chief-endorsed');

        return $ppmp->fresh();
    }

    public function planningApprove(Ppmp $ppmp, User $user, ?string $remarks = null): Ppmp
    {
        $ppmp->update(['planning_by' => $user->id, 'planning_at' => now()]);
        $ppmp->transitionTo(PpmpStatus::BudgetValidation, $remarks, action: 'planning-endorsed');

        return $ppmp->fresh();
    }

    /**
     * "Budget Validation": a dry run against each item's linked Budget
     * Allocation node. Nothing is deducted here; it only reports whether
     * the plan currently fits. Actual utilization happens on final
     * approval so a plan can still be revised/returned beforehand.
     */
    public function validateBudget(Ppmp $ppmp): array
    {
        $issues = [];

        $groups = $ppmp->items()->whereNotNull('budget_allocation_id')->get()->groupBy('budget_allocation_id');

        foreach ($groups as $allocationId => $items) {
            $allocation = BudgetAllocation::find($allocationId);
            $requested = (float) $items->sum('abc');

            if (! $allocation || $requested > $allocation->remaining_balance) {
                $issues[] = [
                    'budget_allocation_id' => $allocationId,
                    'requested' => $requested,
                    'available' => $allocation?->remaining_balance ?? 0,
                ];
            }
        }

        $unassigned = $ppmp->items()->whereNull('budget_allocation_id')->count();
        if ($unassigned > 0) {
            $issues[] = ['unassigned_items' => $unassigned];
        }

        return $issues;
    }

    public function budgetOfficerApprove(Ppmp $ppmp, User $user, ?string $remarks = null): Ppmp
    {
        $issues = $this->validateBudget($ppmp);
        abort_if(! empty($issues), 422, 'PPMP exceeds available budget allocation for one or more items.');

        $ppmp->update(['budget_by' => $user->id, 'budget_at' => now()]);
        $ppmp->transitionTo(PpmpStatus::BacConsolidation, $remarks, action: 'budget-validated');

        return $ppmp->fresh();
    }

    public function bacConsolidate(Ppmp $ppmp, User $user, ?string $remarks = null): Ppmp
    {
        $ppmp->update(['bac_by' => $user->id, 'bac_at' => now()]);
        $ppmp->transitionTo(PpmpStatus::Approved, $remarks, action: 'bac-consolidated');

        return $ppmp;
    }

    /**
     * Final approval: commits the utilization against each linked Budget
     * Allocation node. Wrapped in a transaction so a mid-way
     * BudgetExceededException rolls back every prior deduction.
     */
    public function approve(Ppmp $ppmp, User $user, ?string $remarks = null): Ppmp
    {
        return DB::transaction(function () use ($ppmp, $user, $remarks) {
            $groups = $ppmp->items()->whereNotNull('budget_allocation_id')->get()->groupBy('budget_allocation_id');

            foreach ($groups as $allocationId => $items) {
                $allocation = BudgetAllocation::query()->findOrFail($allocationId);
                $this->allocations->utilize($allocation, (float) $items->sum('abc'));
            }

            $ppmp->update(['approved_by' => $user->id, 'approved_at' => now()]);
            $ppmp->transitionTo(PpmpStatus::Approved, $remarks, action: 'approved', enforce: false);

            $ppmp->annualProcurementPlan?->recalculatePlannedAmount();

            return $ppmp->fresh();
        });
    }

    public function lock(Ppmp $ppmp, User $user): Ppmp
    {
        $ppmp->update(['locked_by' => $user->id, 'locked_at' => now()]);
        $ppmp->transitionTo(PpmpStatus::Locked, 'PPMP locked for the fiscal year.', action: 'locked');

        return $ppmp->fresh();
    }

    public function returnForRevision(Ppmp $ppmp, User $user, string $remarks): Ppmp
    {
        $ppmp->transitionTo(PpmpStatus::ReturnedForRevision, $remarks, action: 'returned-for-revision', enforce: false);

        return $ppmp->fresh();
    }

    /**
     * Creates a Supplemental (additional items) or Amended (revision of
     * existing items) PPMP linked back to the approved original, so the
     * full lineage of changes for the fiscal year remains auditable.
     */
    public function createRevision(Ppmp $original, string $type, User $user): Ppmp
    {
        abort_unless(in_array($type, ['supplemental', 'amended'], true), 422, 'Invalid PPMP revision type.');

        $revision = Ppmp::query()->create([
            'fiscal_year_id' => $original->fiscal_year_id,
            'annual_procurement_plan_id' => $original->annual_procurement_plan_id,
            'division_id' => $original->division_id,
            'ppmp_type' => $type,
            'parent_id' => $original->id,
            'revision_number' => $original->revision_number + 1,
            'title' => $original->title.' ('.Str::title($type).' Rev. '.($original->revision_number + 1).')',
            'status' => PpmpStatus::Draft,
            'prepared_by' => $user->id,
        ]);

        if ($type === 'amended') {
            foreach ($original->items as $item) {
                $revision->items()->create(Arr::only($item->toArray(), [
                    'item_no', 'item_name', 'description', 'specification', 'unit', 'quantity',
                    'estimated_unit_cost', 'abc', 'schedule_start', 'schedule_end', 'mode_of_procurement_id',
                    'fund_source_id', 'pap_id', 'uacs_code_id', 'budget_allocation_id', 'remarks',
                ]));
            }
        }

        return $revision;
    }
}
