<?php

namespace App\Policies;

use App\Models\User;

class AnnualProcurementPlanPolicy extends BasePolicy
{
    use ModuleCrudPolicy;

    protected string $module = 'app';

    public function consolidate(User $user): bool
    {
        return $user->can('app.consolidate');
    }

    public function review(User $user): bool
    {
        return $user->can('app.review');
    }

    public function approve(User $user): bool
    {
        return $user->can('app.approve');
    }

    public function lock(User $user): bool
    {
        return $user->can('app.lock');
    }

    public function unlock(User $user): bool
    {
        return $user->can('app.unlock');
    }
}
