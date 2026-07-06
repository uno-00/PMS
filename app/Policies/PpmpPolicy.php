<?php

namespace App\Policies;

use App\Models\Planning\Ppmp;
use App\Models\User;

class PpmpPolicy extends BasePolicy
{
    protected string $module = 'ppmp';

    public function view(User $user, Ppmp $ppmp): bool
    {
        return $this->canView($user);
    }

    /**
     * Preparers (Division Chief/End User) may only edit their own
     * division's draft PPMPs; reviewers with an explicit review
     * permission can act regardless of division.
     */
    public function update(User $user, Ppmp $ppmp): bool
    {
        if ($ppmp->annualProcurementPlan?->isLocked()) {
            return false;
        }

        if (! $ppmp->isEditable()) {
            return false;
        }

        if ($user->hasRole(['Super Admin', 'Planning Officer'])) {
            return true;
        }

        return $user->can('ppmp.edit') && $user->division_id === $ppmp->division_id;
    }

    public function submit(User $user, Ppmp $ppmp): bool
    {
        return $this->update($user, $ppmp);
    }

    public function divisionChiefReview(User $user, Ppmp $ppmp): bool
    {
        return $user->can('ppmp.review-division') && ($user->division_id === $ppmp->division_id || $user->hasRole('Super Admin'));
    }

    public function planningReview(User $user): bool
    {
        return $user->can('ppmp.review-planning');
    }

    public function budgetValidate(User $user): bool
    {
        return $user->can('ppmp.validate-budget');
    }

    public function bacConsolidate(User $user): bool
    {
        return $user->can('ppmp.consolidate-bac');
    }

    public function approve(User $user): bool
    {
        return $user->can('ppmp.approve');
    }

    public function lock(User $user): bool
    {
        return $user->can('ppmp.lock');
    }
}
