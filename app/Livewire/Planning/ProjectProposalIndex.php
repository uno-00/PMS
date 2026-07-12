<?php

namespace App\Livewire\Planning;

use App\Livewire\Concerns\InteractsWithTableFilters;
use App\Models\Planning\ProjectProposal;
use App\Models\Settings\Division;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ProjectProposalIndex extends Component
{
    use InteractsWithTableFilters, WithPagination;

    #[Url]
    public string $filterControlNo = '';

    #[Url]
    public string $filterTitle = '';

    #[Url]
    public string $filterDivisionId = '';

    #[Url]
    public string $filterPipelineStep = '';

    #[Url]
    public string $filterTotalCost = '';

    #[Url]
    public string $filterPpmp = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', ProjectProposal::class);
    }

    public function resetFilters(): void
    {
        $this->resetTableFilters([
            'filterControlNo',
            'filterTitle',
            'filterDivisionId',
            'filterPipelineStep',
            'filterTotalCost',
            'filterPpmp',
        ]);
    }

    public function render()
    {
        $user = Auth::user();

        $query = ProjectProposal::query()
            ->with(['division', 'fiscalYear', 'preparedBy', 'marketScoping', 'ppmp'])
            ->when(! $user->hasAnyRole(['Super Admin', 'System Admin', 'Planning Officer', 'Budget Officer', 'HOPE', 'Internal Auditor', 'Viewer']), function ($q) use ($user) {
                $q->where('division_id', $user->division_id);
            });

        $this->applyLikeFilter($query, 'control_no', $this->filterControlNo);
        $this->applyLikeFilter($query, 'title', $this->filterTitle);
        $this->applyExactFilter($query, 'division_id', $this->filterDivisionId);
        $this->applyExactFilter($query, 'pipeline_step', $this->filterPipelineStep);
        $this->applyAmountFilter($query, 'total_cost', $this->filterTotalCost);

        if ($this->filterPpmp !== '') {
            $query->whereHas('ppmp', fn ($q) => $q->where('control_no', 'like', '%'.$this->filterPpmp.'%'));
        }

        $this->applyCreatedAtFilter($query);

        $records = $query->orderByDesc('created_at')->paginate(15);

        return view('livewire.planning.project-proposal-index', [
            'records' => $records,
            'divisions' => Division::query()->orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'Project Proposals']);
    }
}
