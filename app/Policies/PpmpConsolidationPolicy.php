<?php

namespace App\Policies;

use App\Enums\PpmpConsolidationStatus;
use App\Models\Planning\PpmpConsolidation;
use App\Models\User;

class PpmpConsolidationPolicy extends BasePolicy
{
    use ModuleCrudPolicy;

    protected string $module = 'ppmp-consolidation';

    public function update(User $user, PpmpConsolidation $consolidation): bool
    {
        return $user->can('ppmp-consolidation.edit') && $consolidation->isEditable();
    }

    public function submit(User $user, PpmpConsolidation $consolidation): bool
    {
        return $user->can('ppmp-consolidation.submit')
            && in_array($consolidation->status, [PpmpConsolidationStatus::Draft, PpmpConsolidationStatus::Validation], true);
    }

    public function planningReview(User $user, PpmpConsolidation $consolidation): bool
    {
        return $user->can('ppmp-consolidation.review-planning')
            && $consolidation->status === PpmpConsolidationStatus::PlanningReview;
    }

    public function budgetReview(User $user, PpmpConsolidation $consolidation): bool
    {
        return $user->can('ppmp-consolidation.review-budget')
            && $consolidation->status === PpmpConsolidationStatus::BudgetReview;
    }

    public function accountingReview(User $user, PpmpConsolidation $consolidation): bool
    {
        return $user->can('ppmp-consolidation.review-accounting')
            && $consolidation->status === PpmpConsolidationStatus::AccountingReview;
    }

    public function bacReview(User $user, PpmpConsolidation $consolidation): bool
    {
        return $user->can('ppmp-consolidation.review-bac')
            && $consolidation->status === PpmpConsolidationStatus::BacReview;
    }

    public function hopeReview(User $user, PpmpConsolidation $consolidation): bool
    {
        return $user->can('ppmp-consolidation.approve-hope')
            && $consolidation->status === PpmpConsolidationStatus::HopeReview;
    }

    public function lock(User $user, PpmpConsolidation $consolidation): bool
    {
        return $user->can('ppmp-consolidation.lock')
            && $consolidation->status === PpmpConsolidationStatus::Approved;
    }

    public function export(User $user): bool
    {
        return $user->can('ppmp-consolidation.export');
    }

    public function cancel(User $user, PpmpConsolidation $consolidation): bool
    {
        return $user->can('ppmp-consolidation.cancel')
            && ! in_array($consolidation->status, [
                PpmpConsolidationStatus::Cancelled,
                PpmpConsolidationStatus::Locked,
            ], true);
    }
}
