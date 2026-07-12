<?php

namespace App\Livewire\Planning;

use App\Models\Planning\ProjectProposal;
use App\Services\Planning\ProjectProposalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProjectProposalShow extends Component
{
    public ProjectProposal $projectProposal;

    public string $remarks = '';

    public bool $showReturnModal = false;

    public function mount(ProjectProposal $projectProposal): void
    {
        Gate::authorize('view', $projectProposal);
        $this->projectProposal = $projectProposal->load([
            'division', 'fiscalYear', 'preparedBy', 'recommendedBy', 'approvedBy',
            'marketScoping', 'ppmp',
        ]);
    }

    public function submit(ProjectProposalService $service): void
    {
        Gate::authorize('submit', $this->projectProposal);
        $service->submitForRecommendation($this->projectProposal, Auth::user());
        session()->flash('status', 'Project Proposal submitted for recommendation.');
        $this->projectProposal->refresh();
    }

    public function recommend(ProjectProposalService $service): void
    {
        Gate::authorize('recommend', $this->projectProposal);
        $service->recommend($this->projectProposal, Auth::user(), $this->remarks);
        session()->flash('status', 'Project Proposal recommended for approval.');
        $this->projectProposal->refresh();
    }

    public function approve(ProjectProposalService $service): void
    {
        Gate::authorize('approve', $this->projectProposal);

        try {
            $service->approve($this->projectProposal, Auth::user(), $this->remarks);
            session()->flash('status', 'Project Proposal approved.');
        } catch (\Throwable $e) {
            $this->addError('approval', $e->getMessage());
        }

        $this->projectProposal->refresh(['ppmp']);
    }

    public function returnForRevision(ProjectProposalService $service): void
    {
        $this->validate(['remarks' => ['required', 'string', 'min:5']]);
        Gate::authorize('returnForRevision', $this->projectProposal);
        $service->returnForRevision($this->projectProposal, Auth::user(), $this->remarks);
        $this->showReturnModal = false;
        session()->flash('status', 'Project Proposal returned for revision.');
        $this->projectProposal->refresh();
    }

    public function render()
    {
        return view('livewire.planning.project-proposal-show')
            ->layout('components.layouts.app', ['title' => $this->projectProposal->title]);
    }
}
