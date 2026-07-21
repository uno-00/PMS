<?php

namespace App\Policies;

use App\Models\User;

/**
 * Generic permission-string-driven policy shared by every simple resource
 * (settings/reference-data models). Concrete module policies (PPMP, PR,
 * CAF, etc.) extend this and override individual abilities only where
 * extra business rules (ownership, workflow stage, division scoping)
 * apply on top of the base Spatie permission check.
 */
abstract class BasePolicy
{
    /** The {module} segment used to build "{module}.{action}" permission strings. */
    protected string $module;

    public function viewAny(User $user): bool
    {
        return $user->can("{$this->module}.view");
    }

    public function create(User $user): bool
    {
        return $user->can("{$this->module}.create") || $user->hasRole('Super Admin');
    }

    protected function canView(User $user): bool
    {
        return $user->can("{$this->module}.view");
    }

    protected function canUpdate(User $user): bool
    {
        return $user->can("{$this->module}.edit") || $user->hasRole('Super Admin');
    }

    protected function canDelete(User $user): bool
    {
        return $user->can("{$this->module}.delete") || $user->hasRole('Super Admin');
    }
}
