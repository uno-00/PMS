<?php

namespace App\Services\Planning;

use App\Enums\PpmpDocumentType;
use App\Enums\PpmpStatus;
use App\Exceptions\BudgetExceededException;
use App\Models\Budget\BudgetAllocation;
use App\Models\Planning\ProjectProposal;
use App\Models\Planning\Ppmp;
use App\Models\Settings\ModeOfProcurement;
use App\Models\User;
use App\Services\Budget\BudgetAllocationService;
use App\Support\RichTextSanitizer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Owns Phase 4 (PPMP) business rules: automatic totals, budget-ceiling
 * validation, the multi-step approval routing (Division Chief -> Planning
 * -> BAC Consolidation -> Procurement Mode -> Budget Validation -> Approved
 * -> Locked), auto-generation from approved Project Proposals, and
 * Supplemental/Amended PPMP creation while preserving full revision lineage.
 */
class PpmpService
{
    public function __construct(protected BudgetAllocationService $allocations) {}

    /**
     * Auto-generates an Indicative PPMP from an approved Project Proposal.
     * Budget allocation and procurement mode are deferred to later workflow stages.
     */
    public function generateFromProjectProposal(ProjectProposal $proposal, User $user): Ppmp
    {
        abort_if($proposal->ppmp_id !== null, 422, 'An Indicative PPMP has already been generated for this Project Proposal.');

        return DB::transaction(function () use ($proposal, $user) {
            $ppmp = Ppmp::query()->create([
                'fiscal_year_id' => $proposal->fiscal_year_id,
                'division_id' => $proposal->division_id,
                'project_proposal_id' => $proposal->id,
                'market_scoping_id' => $proposal->market_scoping_id,
                'ppmp_type' => 'regular',
                'document_type' => PpmpDocumentType::Indicative,
                'title' => $proposal->title,
                'status' => PpmpStatus::Draft,
                'prepared_by' => $user->id,
                'remarks' => 'Auto-generated from Project Proposal '.$proposal->control_no,
            ]);

            $ppmp->items()->create([
                'item_no' => 1,
                'item_name' => $proposal->title,
                'description' => RichTextSanitizer::plainText($proposal->rationale),
                'specification' => RichTextSanitizer::plainText($proposal->objectives),
                'unit' => 'Lot',
                'quantity' => 1,
                'estimated_unit_cost' => $proposal->total_cost,
                'abc' => $proposal->total_cost,
                'schedule_start' => null,
                'schedule_end' => null,
                'remarks' => RichTextSanitizer::plainText($proposal->budgetary_requirement),
            ]);

            $ppmp->recalculateTotal();
            $ppmp->transitionTo(
                PpmpStatus::Draft,
                'Indicative PPMP auto-generated from Project Proposal '.$proposal->control_no.'.',
                action: 'auto-generated',
                enforce: false
            );

            return $ppmp->fresh();
        });
    }

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
        $ppmp->transitionTo(PpmpStatus::BacConsolidation, $remarks, action: 'planning-endorsed');

        return $ppmp->fresh();
    }

    public function bacConsolidate(Ppmp $ppmp, User $user, ?string $remarks = null): Ppmp
    {
        $ppmp->update(['bac_by' => $user->id, 'bac_at' => now()]);
        $ppmp->transitionTo(PpmpStatus::ProcurementModeReview, $remarks, action: 'bac-consolidated');

        return $ppmp->fresh();
    }

    /**
     * BAC recommends mode of procurement for each line item before budget linkage.
     *
     * @param  array<int, array{item_id: string, mode_of_procurement_id: string}>  $itemModes
     */
    public function recommendProcurementModes(Ppmp $ppmp, User $user, array $itemModes, ?string $remarks = null): Ppmp
    {
        abort_if($ppmp->status !== PpmpStatus::ProcurementModeReview, 422, 'PPMP is not at Procurement Mode Review stage.');

        foreach ($itemModes as $row) {
            abort_if(empty($row['mode_of_procurement_id']), 422, 'All items must have a recommended mode of procurement.');
            $ppmp->items()->where('id', $row['item_id'])->update([
                'mode_of_procurement_id' => $row['mode_of_procurement_id'],
            ]);
        }

        $ppmp->update(['procurement_mode_by' => $user->id, 'procurement_mode_at' => now()]);
        $ppmp->transitionTo(PpmpStatus::BudgetValidation, $remarks ?? 'BAC recommended mode of procurement.', action: 'procurement-mode-recommended');

        return $ppmp->fresh();
    }

    /**
     * Auto-suggest procurement modes based on ABC thresholds.
     */
    public function suggestProcurementModes(Ppmp $ppmp): array
    {
        return $ppmp->items()->get()->map(function ($item) {
            $suggested = ModeOfProcurement::resolveForAmount((float) $item->abc);

            return [
                'item_id' => $item->id,
                'item_name' => $item->item_name,
                'abc' => $item->abc,
                'suggested_mode_id' => $suggested?->id,
                'suggested_mode_name' => $suggested?->name,
                'current_mode_id' => $item->mode_of_procurement_id,
            ];
        })->all();
    }

    /**
     * Budget Officer links budget allocations to PPMP items at validation stage.
     *
     * @param  array<int, array{item_id: string, budget_allocation_id: string}>  $itemBudgets
     */
    public function linkBudgetAllocations(Ppmp $ppmp, array $itemBudgets): Ppmp
    {
        abort_if($ppmp->status !== PpmpStatus::BudgetValidation, 422, 'PPMP is not at Budget Validation stage.');

        $allocations = BudgetAllocation::query()
            ->whereIn('id', collect($itemBudgets)->pluck('budget_allocation_id'))
            ->get()
            ->keyBy('id');

        foreach ($itemBudgets as $row) {
            abort_if(empty($row['budget_allocation_id']), 422, 'All items must have a budget allocation linked.');
            $allocation = $allocations->get($row['budget_allocation_id']);
            abort_if(! $allocation, 422, 'Invalid budget allocation selected.');

            $ppmp->items()->where('id', $row['item_id'])->update([
                'budget_allocation_id' => $allocation->id,
                'fund_source_id' => $allocation->fund_source_id,
                'pap_id' => $allocation->pap_id,
                'uacs_code_id' => $allocation->uacs_code_id,
            ]);
        }

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
        abort_if(! empty($issues), 422, 'PPMP exceeds available budget allocation for one or more items, or has unlinked items.');

        $ppmp->update(['budget_by' => $user->id, 'budget_at' => now()]);
        $ppmp->transitionTo(PpmpStatus::Approved, $remarks ?? 'Budget linkage supported by Budget Officer.', action: 'budget-validated');

        return $ppmp->fresh();
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
            'project_proposal_id' => $original->project_proposal_id,
            'market_scoping_id' => $original->market_scoping_id,
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
