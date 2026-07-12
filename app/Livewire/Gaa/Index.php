<?php

namespace App\Livewire\Gaa;

use App\Livewire\Concerns\InteractsWithTableFilters;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Settings\FiscalYear;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use InteractsWithTableFilters, WithPagination;

    #[Url]
    public string $filterFiscalYearId = '';

    #[Url]
    public string $filterTitle = '';

    #[Url]
    public string $filterReferenceNo = '';

    #[Url]
    public string $filterTotalAmount = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', GeneralAppropriationsAct::class);
    }

    public function resetFilters(): void
    {
        $this->resetTableFilters([
            'filterFiscalYearId',
            'filterTitle',
            'filterReferenceNo',
            'filterTotalAmount',
            'status',
        ]);
    }

    public function render()
    {
        $query = GeneralAppropriationsAct::query()
            ->with('fiscalYear');

        $this->applyExactFilter($query, 'fiscal_year_id', $this->filterFiscalYearId);
        $this->applyLikeFilter($query, 'title', $this->filterTitle);
        $this->applyLikeFilter($query, 'reference_no', $this->filterReferenceNo);
        $this->applyAmountFilter($query, 'total_amount', $this->filterTotalAmount);
        $this->applyExactFilter($query, 'status', $this->status);
        $this->applyCreatedAtFilter($query);

        $gaas = $query->orderByDesc('created_at')->paginate(15);

        $fiscalYearsWithoutGaa = FiscalYear::query()
            ->whereDoesntHave('gaa')
            ->orderByDesc('year')
            ->get();

        return view('livewire.gaa.index', [
            'gaas' => $gaas,
            'fiscalYearsWithoutGaa' => $fiscalYearsWithoutGaa,
            'fiscalYears' => FiscalYear::query()->orderByDesc('year')->get(),
        ])->layout('components.layouts.app', ['title' => 'General Appropriations Act']);
    }
}
