<?php

namespace App\Services\Budget;

use App\Exceptions\BudgetExceededException;
use App\Models\Budget\BudgetAllocation;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Owns every rule in Phase 3 (Budget Distribution): allocating funds down
 * the Department -> Division -> Office -> Cost Center hierarchy, always
 * keeping an accurate "remaining balance" at every node, and refusing any
 * allocation or utilization that would exceed what a parent node actually
 * has left. This is the single place "No overallocation allowed" is
 * enforced for the whole system.
 */
class BudgetAllocationService
{
    /**
     * Seeds top-level, per-department budget allocation pools from an
     * approved & distributed GAA's line items. Each unique combination of
     * department + PAP + fund source becomes a root allocation node that
     * the Budget Office can then sub-allocate further.
     */
    public function seedFromGaa(GeneralAppropriationsAct $gaa): void
    {
        $grouped = $gaa->lineItems()
            ->selectRaw('department_id, division_id, pap_id, fund_source_id, uacs_code_id, sum(amount) as total')
            ->groupBy('department_id', 'division_id', 'pap_id', 'fund_source_id', 'uacs_code_id')
            ->get();

        foreach ($grouped as $row) {
            BudgetAllocation::query()->create([
                'fiscal_year_id' => $gaa->fiscal_year_id,
                'gaa_id' => $gaa->id,
                'level' => 'department',
                'department_id' => $row->department_id,
                'division_id' => $row->division_id,
                'pap_id' => $row->pap_id,
                'fund_source_id' => $row->fund_source_id,
                'uacs_code_id' => $row->uacs_code_id,
                'allocated_amount' => $row->total,
                'remarks' => 'Seeded from approved GAA distribution.',
            ]);
        }
    }

    /**
     * Sub-allocate a portion of a parent node's remaining balance to a
     * more granular level (division, office, cost center, or a specific
     * PAP/fund-source combination). Throws when the request would exceed
     * what the parent has left.
     */
    public function allocate(BudgetAllocation $parent, array $attributes, User $user): BudgetAllocation
    {
        $amount = (float) $attributes['allocated_amount'];

        return DB::transaction(function () use ($parent, $attributes, $amount, $user) {
            $parent = BudgetAllocation::query()->lockForUpdate()->findOrFail($parent->id);

            if ($amount > $parent->remaining_balance) {
                throw BudgetExceededException::forAllocation($amount, $parent->remaining_balance);
            }

            return BudgetAllocation::query()->create(array_merge($attributes, [
                'fiscal_year_id' => $parent->fiscal_year_id,
                'parent_id' => $parent->id,
                'gaa_id' => $parent->gaa_id,
                'pap_id' => $attributes['pap_id'] ?? $parent->pap_id,
                'fund_source_id' => $attributes['fund_source_id'] ?? $parent->fund_source_id,
                'uacs_code_id' => $attributes['uacs_code_id'] ?? $parent->uacs_code_id,
                'created_by' => $user->id,
            ]));
        });
    }

    /**
     * Deduct funds directly utilized against a leaf allocation (e.g. by an
     * approved PPMP line or Purchase Request). Refuses to push utilization
     * past the node's remaining balance.
     */
    public function utilize(BudgetAllocation $allocation, float $amount): BudgetAllocation
    {
        return DB::transaction(function () use ($allocation, $amount) {
            $allocation = BudgetAllocation::query()->lockForUpdate()->findOrFail($allocation->id);

            if ($amount > $allocation->remaining_balance) {
                throw BudgetExceededException::forAllocation($amount, $allocation->remaining_balance);
            }

            $allocation->increment('utilized_amount', $amount);

            return $allocation->fresh();
        });
    }

    /**
     * Return previously utilized funds (e.g. a Purchase Request was
     * rejected/cancelled after its PPMP balance was deducted).
     */
    public function release(BudgetAllocation $allocation, float $amount): BudgetAllocation
    {
        return DB::transaction(function () use ($allocation, $amount) {
            $allocation = BudgetAllocation::query()->lockForUpdate()->findOrFail($allocation->id);
            $allocation->decrement('utilized_amount', min($amount, (float) $allocation->utilized_amount));

            return $allocation->fresh();
        });
    }

    public function totalAllocated(string $fiscalYearId): float
    {
        return (float) BudgetAllocation::query()
            ->where('fiscal_year_id', $fiscalYearId)
            ->whereNull('parent_id')
            ->sum('allocated_amount');
    }

    public function totalUtilized(string $fiscalYearId): float
    {
        return (float) BudgetAllocation::query()
            ->where('fiscal_year_id', $fiscalYearId)
            ->sum('utilized_amount');
    }
}
