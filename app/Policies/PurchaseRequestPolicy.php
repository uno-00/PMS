<?php

namespace App\Policies;

use App\Models\Procurement\PurchaseRequest;
use App\Models\User;

class PurchaseRequestPolicy extends BasePolicy
{
    protected string $module = 'purchase-request';

    public function view(User $user, PurchaseRequest $pr): bool
    {
        return $this->canView($user);
    }

    public function update(User $user, PurchaseRequest $pr): bool
    {
        if (! $pr->isEditable()) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->can('purchase-request.edit') && $user->division_id === $pr->division_id;
    }

    public function divisionChiefReview(User $user): bool
    {
        return $user->can('purchase-request.review-division');
    }

    public function planningReview(User $user): bool
    {
        return $user->can('purchase-request.review-planning');
    }

    public function budgetReview(User $user): bool
    {
        return $user->can('purchase-request.review-budget');
    }

    public function hopeApprove(User $user): bool
    {
        return $user->can('purchase-request.approve-hope');
    }
}
