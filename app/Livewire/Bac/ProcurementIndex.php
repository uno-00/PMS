<?php

namespace App\Livewire\Bac;

use App\Livewire\Concerns\InteractsWithTableFilters;
use App\Models\Bac\Procurement;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ProcurementIndex extends Component
{
    use InteractsWithTableFilters, WithPagination;

    #[Url]
    public string $filterCaseNo = '';

    #[Url]
    public string $filterTitle = '';

    #[Url]
    public string $filterMode = '';

    #[Url]
    public string $filterAbc = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('bac-calendar.view');
    }

    public function resetFilters(): void
    {
        $this->resetTableFilters([
            'filterCaseNo',
            'filterTitle',
            'filterMode',
            'filterAbc',
            'status',
        ]);
    }

    public function render()
    {
        $query = Procurement::query()
            ->with(['purchaseRequest.division', 'modeOfProcurement']);

        $this->applyLikeFilter($query, 'case_no', $this->filterCaseNo);
        $this->applyLikeFilter($query, 'title', $this->filterTitle);

        if ($this->filterMode !== '') {
            $query->whereHas('modeOfProcurement', fn ($q) => $q->where('name', 'like', '%'.$this->filterMode.'%'));
        }

        $this->applyAmountFilter($query, 'abc', $this->filterAbc);
        $this->applyExactFilter($query, 'status', $this->status);
        $this->applyCreatedAtFilter($query);

        $procurements = $query->orderByDesc('created_at')->paginate(15);

        return view('livewire.bac.procurement-index', compact('procurements'))
            ->layout('components.layouts.app', ['title' => 'Procurement Cases']);
    }
}
