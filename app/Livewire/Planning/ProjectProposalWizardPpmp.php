<?php

namespace App\Livewire\Planning;

use App\Enums\ProjectProposalPipelineStep;
use App\Models\Planning\ProjectProposal;
use App\Services\Planning\ProjectProposalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ProjectProposalWizardPpmp extends Component
{
    public ProjectProposal $projectProposal;

    public function mount(ProjectProposal $projectProposal): void
    {
        Gate::authorize('view', $projectProposal);
        abort_unless(
            in_array($projectProposal->pipeline_step, [
                ProjectProposalPipelineStep::IndicativePpmp,
                ProjectProposalPipelineStep::Completed,
            ], true),
            403,
            'Complete Step 2: Market Scoping first.'
        );

        $this->projectProposal = $projectProposal->load(['marketScoping', 'ppmp', 'division', 'fiscalYear']);
    }

    public function generateIndicativePpmp(ProjectProposalService $service): void
    {
        Gate::authorize('update', $this->projectProposal);

        try {
            $service->generateIndicativePpmp($this->projectProposal, Auth::user());
            $this->projectProposal = $this->projectProposal->fresh(['marketScoping', 'ppmp', 'division', 'fiscalYear']);
            session()->flash('status', 'Indicative PPMP generated successfully. You may print the pipeline documents below.');
        } catch (\Throwable $e) {
            $this->addError('generation', $e->getMessage());
        }
    }

    public function render()
    {
        $preview = app(ProjectProposalService::class)->indicativePpmpPreview($this->projectProposal);

        return view('livewire.planning.project-proposal-wizard-ppmp', [
            'preview' => $preview,
        ])->layout('components.layouts.app', [
            'title' => 'Step 3: Indicative PPMP',
        ]);
    }
}
