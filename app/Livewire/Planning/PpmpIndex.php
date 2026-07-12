<?php

namespace App\Livewire\Planning;

use App\Enums\PpmpDocumentType;
use App\Livewire\Concerns\InteractsWithTableFilters;
use App\Models\Planning\Ppmp;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PpmpIndex extends Component
{
    use InteractsWithTableFilters, WithPagination;

    #[Url]
    public string $filterControlNo = '';

    #[Url]
    public string $filterTitle = '';

    #[Url]
    public string $filterDocumentType = '';

    #[Url]
    public string $filterDivisionId = '';

    #[Url]
    public string $filterFiscalYearId = '';

    #[Url]
    public string $filterTotalAbc = '';

    #[Url]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Ppmp::class);
    }

    public function resetFilters(): void
    {
        $this->resetTableFilters([
            'filterControlNo',
            'filterTitle',
            'filterDocumentType',
            'filterDivisionId',
            'filterFiscalYearId',
            'filterTotalAbc',
            'status',
        ]);
    }

    public function render()
    {
        $user = Auth::user();

        $query = Ppmp::query()
            ->with(['division', 'fiscalYear'])
            ->when(! $user->hasAnyRole(['Super Admin', 'System Admin', 'Planning Officer', 'Budget Officer', 'HOPE', 'Internal Auditor', 'Viewer']), function ($q) use ($user) {
                $q->where('division_id', $user->division_id);
            });

        $this->applyLikeFilter($query, 'control_no', $this->filterControlNo);
        $this->applyLikeFilter($query, 'title', $this->filterTitle);
        $this->applyExactFilter($query, 'document_type', $this->filterDocumentType);
        $this->applyExactFilter($query, 'division_id', $this->filterDivisionId);
        $this->applyExactFilter($query, 'fiscal_year_id', $this->filterFiscalYearId);
        $this->applyAmountFilter($query, 'total_abc', $this->filterTotalAbc);
        $this->applyExactFilter($query, 'status', $this->status);
        $this->applyCreatedAtFilter($query);

        $ppmps = $query->orderByDesc('created_at')->paginate(15);

        return view('livewire.planning.ppmp-index', [
            'ppmps' => $ppmps,
            'divisions' => Division::query()->orderBy('name')->get(),
            'fiscalYears' => FiscalYear::query()->orderByDesc('year')->get(),
            'documentTypes' => PpmpDocumentType::options(),
        ])->layout('components.layouts.app', ['title' => 'Project Procurement Management Plans']);
    }
}
