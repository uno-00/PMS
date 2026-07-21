<?php

namespace App\Livewire\Planning;

use App\Livewire\Concerns\InteractsWithTableFilters;
use App\Models\Planning\MarketScoping;
use App\Models\Settings\FiscalYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class MarketScopingIndex extends Component
{
    use InteractsWithTableFilters, WithPagination;

    #[Url]
    public string $filterControlNo = '';

    #[Url]
    public string $filterProjectName = '';

    #[Url]
    public string $filterEndUserUnit = '';

    #[Url]
    public string $filterFiscalYearId = '';

    #[Url]
    public string $filterEstimatedBudget = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', MarketScoping::class);
    }

    public function resetFilters(): void
    {
        $this->resetTableFilters([
            'filterControlNo',
            'filterProjectName',
            'filterEndUserUnit',
            'filterFiscalYearId',
            'filterEstimatedBudget',
            'status',
        ]);
    }

    public function render()
    {
        $user = Auth::user();

        $query = MarketScoping::query()
            ->with(['division', 'fiscalYear', 'preparedBy'])
            ->when(! $user->hasAnyRole(['Super Admin', 'System Admin', 'Planning Officer', 'Budget Officer', 'HOPE', 'Internal Auditor', 'Viewer']), function ($q) use ($user) {
                $q->where('division_id', $user->division_id);
            });

        $this->applyLikeFilter($query, 'control_no', $this->filterControlNo);
        $this->applyLikeFilter($query, 'project_name', $this->filterProjectName);

        if ($this->filterEndUserUnit !== '') {
            $query->where(function ($q) {
                $q->where('end_user_unit', 'like', '%'.$this->filterEndUserUnit.'%')
                    ->orWhereHas('division', fn ($inner) => $inner->where('name', 'like', '%'.$this->filterEndUserUnit.'%'));
            });
        }

        $this->applyExactFilter($query, 'fiscal_year_id', $this->filterFiscalYearId);
        $this->applyAmountFilter($query, 'estimated_budget', $this->filterEstimatedBudget);
        $this->applyExactFilter($query, 'status', $this->status);
        $this->applyCreatedAtFilter($query);

        $records = $query->orderByDesc('created_at')->paginate(15);

        return view('livewire.planning.market-scoping-index', [
            'records' => $records,
            'fiscalYears' => FiscalYear::query()->orderByDesc('year')->get(),
        ])->layout('components.layouts.app', ['title' => 'Market Scoping']);
    }
}
