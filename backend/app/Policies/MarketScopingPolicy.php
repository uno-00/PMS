<?php

namespace App\Policies;

use App\Models\Planning\MarketScoping;
use App\Models\User;

class MarketScopingPolicy extends BasePolicy
{
    protected string $module = 'market-scoping';

    public function view(User $user, MarketScoping $marketScoping): bool
    {
        return $this->canView($user);
    }

    public function update(User $user, MarketScoping $marketScoping): bool
    {
        if (! $marketScoping->isEditable()) {
            return false;
        }

        if ($user->hasRole(['Super Admin', 'Planning Officer'])) {
            return true;
        }

        return $user->can('market-scoping.edit') && $user->division_id === $marketScoping->division_id;
    }

    public function approve(User $user, MarketScoping $marketScoping): bool
    {
        return $marketScoping->isEditable()
            && ($user->can('market-scoping.approve') || $user->hasRole(['Super Admin', 'Division Chief']));
    }

    public function delete(User $user, MarketScoping $marketScoping): bool
    {
        return $marketScoping->isEditable() && $this->canDelete($user);
    }
}
