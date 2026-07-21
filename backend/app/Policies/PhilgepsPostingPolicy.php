<?php

namespace App\Policies;

use App\Enums\PhilgepsPostingStatus;
use App\Models\Bac\PhilgepsPosting;
use App\Models\User;

/**
 * PhilGEPS posting permissions. API-originated postings (is_manual = false)
 * and postings that have already Closed/Cancelled are read-only: only
 * manually-created postings still in the Published state may be edited or
 * removed.
 */
class PhilgepsPostingPolicy extends BasePolicy
{
    use ModuleCrudPolicy;

    protected string $module = 'philgeps';

    public function update(User $user, PhilgepsPosting $posting): bool
    {
        return $this->canUpdate($user)
            && $posting->is_manual
            && $posting->status === PhilgepsPostingStatus::Published;
    }

    public function delete(User $user, PhilgepsPosting $posting): bool
    {
        return $this->canDelete($user)
            && $posting->is_manual
            && $posting->status === PhilgepsPostingStatus::Published;
    }
}
