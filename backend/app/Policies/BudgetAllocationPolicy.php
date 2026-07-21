<?php

namespace App\Policies;

use App\Models\Budget\BudgetAllocation;
use App\Models\User;

/**
 * Budget allocation permissions. Structural guards (cannot delete a node
 * with children, cannot delete a utilized allocation, cannot over-allocate
 * on edit) are enforced at runtime by BudgetAllocationService and surfaced
 * by the Livewire layer — the policy only owns the RBAC permission check.
 */
class BudgetAllocationPolicy extends BasePolicy
{
    use ModuleCrudPolicy;

    protected string $module = 'budget-allocation';

    public function create(User $user): bool
    {
        return $user->can('budget-allocation.allocate') || $user->hasRole('Super Admin');
    }
}
