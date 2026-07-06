<?php

namespace App\Policies;

use App\Models\User;

class CafPolicy extends BasePolicy
{
    use ModuleCrudPolicy;

    protected string $module = 'caf';

    public function certify(User $user): bool
    {
        return $user->can('caf.certify');
    }

    public function approve(User $user): bool
    {
        return $user->can('caf.approve');
    }

    public function print(User $user): bool
    {
        return $user->can('caf.print');
    }
}
