<?php

namespace App\Policies;

use App\Models\Supplier\Bidder;
use App\Models\User;

class BidderPolicy extends BasePolicy
{
    protected string $module = 'bidder';

    public function view(User $user, ?Bidder $bidder = null): bool
    {
        if ($bidder && $user->id === $bidder->user_id) {
            return true;
        }

        return $user->can('bidder.view');
    }

    public function update(User $user, ?Bidder $bidder = null): bool
    {
        if ($bidder && $user->id === $bidder->user_id) {
            return true;
        }

        return $user->can('bidder.manage');
    }

    public function verify(User $user): bool
    {
        return $user->can('bidder.verify');
    }
}
