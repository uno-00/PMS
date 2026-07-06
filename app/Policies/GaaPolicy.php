<?php

namespace App\Policies;

use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\User;

class GaaPolicy extends BasePolicy
{
    use ModuleCrudPolicy;

    protected string $module = 'gaa';

    public function upload(User $user): bool
    {
        return $user->can('gaa.upload');
    }

    public function validateBudget(User $user, GeneralAppropriationsAct $gaa): bool
    {
        return $user->can('gaa.validate');
    }

    public function approve(User $user, GeneralAppropriationsAct $gaa): bool
    {
        return $user->can('gaa.approve');
    }

    public function distribute(User $user, GeneralAppropriationsAct $gaa): bool
    {
        return $user->can('gaa.distribute');
    }
}
