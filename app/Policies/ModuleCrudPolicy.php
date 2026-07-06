<?php

namespace App\Policies;

use App\Models\User;

/**
 * Default CRUD permission checks for module policies that do not
 * need model-specific authorization rules.
 */
trait ModuleCrudPolicy
{
    public function view(User $user, $model = null): bool
    {
        return $this->canView($user);
    }

    public function update(User $user, $model = null): bool
    {
        return $this->canUpdate($user);
    }

    public function delete(User $user, $model = null): bool
    {
        return $this->canDelete($user);
    }
}
