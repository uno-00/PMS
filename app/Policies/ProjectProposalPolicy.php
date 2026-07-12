<?php

namespace App\Policies;

use App\Models\Planning\ProjectProposal;
use App\Models\User;

class ProjectProposalPolicy extends BasePolicy
{
    protected string $module = 'project-proposal';

    public function view(User $user, ProjectProposal $projectProposal): bool
    {
        return $this->canView($user);
    }

    public function update(User $user, ProjectProposal $projectProposal): bool
    {
        if (! $projectProposal->isEditable()) {
            return false;
        }

        if ($user->hasRole(['Super Admin', 'Planning Officer'])) {
            return true;
        }

        return $user->can('project-proposal.edit') && $user->division_id === $projectProposal->division_id;
    }

    public function submit(User $user, ProjectProposal $projectProposal): bool
    {
        return $this->update($user, $projectProposal);
    }

    public function recommend(User $user, ProjectProposal $projectProposal): bool
    {
        return $projectProposal->status->value === 'for_recommendation'
            && $user->can('project-proposal.recommend');
    }

    public function approve(User $user, ProjectProposal $projectProposal): bool
    {
        return $projectProposal->status->value === 'for_recommendation'
            && $user->can('project-proposal.approve');
    }

    public function returnForRevision(User $user, ProjectProposal $projectProposal): bool
    {
        return $projectProposal->status->value === 'for_recommendation'
            && ($user->can('project-proposal.recommend') || $user->can('project-proposal.approve'));
    }

    public function createFromMarketScoping(User $user): bool
    {
        return false;
    }
}
