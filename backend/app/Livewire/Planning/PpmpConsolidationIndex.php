<?php

namespace App\Livewire\Planning;

use App\Enums\PpmpConsolidationStatus;
use App\Enums\PpmpDocumentType;
use App\Models\Planning\Ppmp;
use App\Models\Planning\PpmpConsolidation;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Services\Planning\PpmpConsolidationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PpmpConsolidationIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Url]
    public string $documentType = '';

    #[Url]
    public string $fiscalYearId = '';

    public ?string $cancelConsolidationId = null;

    public string $cancelRemarks = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', PpmpConsolidation::class);
    }

    public function promptCancel(string $consolidationId): void
    {
        $consolidation = PpmpConsolidation::query()->findOrFail($consolidationId);
        Gate::authorize('cancel', $consolidation);
        $this->cancelConsolidationId = $consolidationId;
        $this->cancelRemarks = '';
    }

    public function cancelConsolidation(PpmpConsolidationService $service): void
    {
        $consolidation = PpmpConsolidation::query()->findOrFail($this->cancelConsolidationId);
        Gate::authorize('cancel', $consolidation);

        $this->validate(['cancelRemarks' => ['required', 'string', 'min:5']]);

        $service->cancel($consolidation, Auth::user(), $this->cancelRemarks);

        $this->cancelConsolidationId = null;
        $this->cancelRemarks = '';

        session()->flash('status', 'Consolidation '.$consolidation->reference_no.' cancelled.');
    }

    public function render()
    {
        $consolidations = PpmpConsolidation::query()
            ->with(['fiscalYear', 'creator'])
            ->withCount(['sourcePpmps', 'items'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->documentType, fn ($q) => $q->where('document_type', $this->documentType))
            ->when($this->fiscalYearId, fn ($q) => $q->where('fiscal_year_id', $this->fiscalYearId))
            ->orderByDesc('updated_at')
            ->paginate(12);

        $fiscalYears = FiscalYear::query()->orderByDesc('year')->get();

        $stats = [
            'total_ppmps_submitted' => Ppmp::query()->whereIn('status', ['approved', 'locked'])->count(),
            'consolidated' => PpmpConsolidation::query()->whereIn('status', ['approved', 'locked'])->count(),
            'pending' => PpmpConsolidation::query()->whereNotIn('status', ['approved', 'locked'])->count(),
            'total_budget' => (float) PpmpConsolidation::query()->sum('total_budget'),
            'pending_approvals' => PpmpConsolidation::query()->whereIn('status', [
                PpmpConsolidationStatus::PlanningReview->value,
                PpmpConsolidationStatus::BudgetReview->value,
                PpmpConsolidationStatus::AccountingReview->value,
                PpmpConsolidationStatus::BacReview->value,
                PpmpConsolidationStatus::HopeReview->value,
            ])->count(),
        ];

        $budgetByDivision = PpmpConsolidation::query()
            ->whereIn('status', ['approved', 'locked', 'draft', 'validation'])
            ->with(['items.division'])
            ->get()
            ->flatMap(fn ($c) => $c->items)
            ->groupBy(fn ($item) => $item->division?->name ?? 'Unassigned')
            ->map(fn ($items) => $items->sum(fn ($item) => $item->lineAbc()))
            ->sortDesc()
            ->take(8);

        $budgetByFund = PpmpConsolidation::query()
            ->with(['items.fundSource'])
            ->get()
            ->flatMap(fn ($c) => $c->items)
            ->groupBy(fn ($item) => $item->fundSource?->name ?? 'Unassigned')
            ->map(fn ($items) => $items->sum(fn ($item) => $item->lineAbc()))
            ->sortDesc()
            ->take(8);

        return view('livewire.planning.ppmp-consolidation-index', [
            'consolidations' => $consolidations,
            'fiscalYears' => $fiscalYears,
            'documentTypes' => PpmpDocumentType::options(),
            'statuses' => collect(PpmpConsolidationStatus::cases())
                ->mapWithKeys(fn ($s) => [$s->value => $s->label()])
                ->all(),
            'stats' => $stats,
            'budgetByDivision' => $budgetByDivision,
            'budgetByFund' => $budgetByFund,
        ])->layout('components.layouts.app', ['title' => 'PPMP Consolidation']);
    }
}
