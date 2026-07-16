<?php

namespace App\Livewire\Planning;

use App\Enums\PpmpConsolidationStatus;
use App\Enums\PpmpConsolidationStep;
use App\Enums\PpmpDocumentType;
use App\Models\Planning\PpmpConsolidation;
use App\Models\Settings\Division;
use App\Models\Settings\FiscalYear;
use App\Models\Settings\FundSource;
use App\Models\Settings\Office;
use App\Services\Planning\PpmpConsolidationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PpmpConsolidationWizard extends Component
{
    public ?PpmpConsolidation $consolidation = null;

    #[Url]
    public int $step = 1;

    public string $title = '';

    public string $fiscal_year_id = '';

    public string $document_type = 'indicative';

    public string $search = '';

    public string $filter_division_id = '';

    public string $filter_office_id = '';

    public string $filter_fund_source_id = '';

    public string $filter_expense_class = '';

    /** @var array<int, string> */
    public array $selectedPpmpIds = [];

    public bool $mergeDuplicates = true;

    public string $remarks = '';

    public bool $showReturnModal = false;

    public bool $showCancelModal = false;

    public function mount(?PpmpConsolidation $consolidation = null): void
    {
        if ($consolidation && $consolidation->exists) {
            Gate::authorize('view', $consolidation);
            $this->consolidation = $consolidation->load(['sourcePpmps', 'items', 'bp2020Lines', 'wfpLines']);
            $this->title = $consolidation->title;
            $this->fiscal_year_id = $consolidation->fiscal_year_id;
            $this->document_type = $consolidation->document_type->value;
            $this->selectedPpmpIds = $consolidation->sourcePpmps->pluck('id')->all();
            $this->step = max(1, min(7, (int) $consolidation->current_step));
        } else {
            Gate::authorize('create', PpmpConsolidation::class);
            $this->fiscal_year_id = FiscalYear::query()->where('is_current', true)->value('id') ?? '';
        }
    }

    public function selectAllEligible(PpmpConsolidationService $service): void
    {
        $this->selectedPpmpIds = $service->eligiblePpmpsQuery(
            $this->fiscal_year_id,
            PpmpDocumentType::from($this->document_type),
            $this->consolidation?->id,
        )->pluck('id')->all();
    }

    public function unselectAll(): void
    {
        $this->selectedPpmpIds = [];
    }

    public function saveStep1(PpmpConsolidationService $service): void
    {
        if ($this->consolidation) {
            Gate::authorize('update', $this->consolidation);
            $this->consolidation->update(['title' => $this->title ?: $this->consolidation->title]);
            $service->syncSources($this->consolidation, $this->selectedPpmpIds);
            $this->consolidation->refresh();
        } else {
            $this->consolidation = $service->createDraft(
                Auth::user(),
                $this->fiscal_year_id,
                PpmpDocumentType::from($this->document_type),
                $this->selectedPpmpIds,
                $this->title ?: null,
            );
        }

        $service->generateConsolidatedItems($this->consolidation, $this->mergeDuplicates);
        $this->consolidation->refresh();

        session()->flash('status', count($this->selectedPpmpIds).' PPMP(s) consolidated.');
        $this->step = 2;
        $this->consolidation->update(['current_step' => 2]);
    }

    public function generateConsolidated(PpmpConsolidationService $service): void
    {
        Gate::authorize('update', $this->consolidation);
        $service->generateConsolidatedItems($this->consolidation, $this->mergeDuplicates);
        $this->consolidation->refresh();
        session()->flash('status', 'Consolidated PPMP generated.');
        $this->step = 2;
    }

    public function generateBp2020(PpmpConsolidationService $service): void
    {
        Gate::authorize('update', $this->consolidation);
        $service->generateBp2020($this->consolidation);
        $this->consolidation->refresh();
        session()->flash('status', 'BP Form 2020 generated.');
        $this->step = 3;
    }

    public function generateWfp(PpmpConsolidationService $service): void
    {
        Gate::authorize('update', $this->consolidation);
        $service->generateWfp($this->consolidation);
        $this->consolidation->refresh();
        session()->flash('status', 'Work and Financial Plan generated.');
        $this->step = 4;
    }

    public function runValidation(PpmpConsolidationService $service): void
    {
        Gate::authorize('update', $this->consolidation);
        $issues = $service->validate($this->consolidation);
        $this->consolidation->refresh();
        $this->step = 5;

        if ($issues === []) {
            session()->flash('status', 'Validation passed with no issues.');
        } else {
            session()->flash('warning', count($issues).' validation issue(s) detected.');
        }
    }

    public function submitForApproval(PpmpConsolidationService $service): void
    {
        Gate::authorize('submit', $this->consolidation);
        $service->submitForApproval($this->consolidation, Auth::user());
        $this->consolidation->refresh();
        $this->step = 6;
        session()->flash('status', 'Consolidation submitted for approval.');
    }

    public function planningApprove(PpmpConsolidationService $service): void
    {
        Gate::authorize('planningReview', $this->consolidation);
        $service->planningApprove($this->consolidation, Auth::user(), $this->remarks);
        $this->consolidation->refresh();
        session()->flash('status', 'Planning review completed.');
    }

    public function budgetApprove(PpmpConsolidationService $service): void
    {
        Gate::authorize('budgetReview', $this->consolidation);
        $service->budgetApprove($this->consolidation, Auth::user(), $this->remarks);
        $this->consolidation->refresh();
        session()->flash('status', 'Budget review completed.');
    }

    public function accountingApprove(PpmpConsolidationService $service): void
    {
        Gate::authorize('accountingReview', $this->consolidation);
        $service->accountingApprove($this->consolidation, Auth::user(), $this->remarks);
        $this->consolidation->refresh();
        session()->flash('status', 'Accounting review completed.');
    }

    public function bacApprove(PpmpConsolidationService $service): void
    {
        Gate::authorize('bacReview', $this->consolidation);
        $service->bacApprove($this->consolidation, Auth::user(), $this->remarks);
        $this->consolidation->refresh();
        session()->flash('status', 'BAC review completed.');
    }

    public function hopeApprove(PpmpConsolidationService $service): void
    {
        Gate::authorize('hopeReview', $this->consolidation);
        $service->hopeApprove($this->consolidation, Auth::user(), $this->remarks);
        $this->consolidation->refresh();
        $this->step = 7;
        session()->flash('status', 'HoPE approved the consolidation.');
    }

    public function returnForRevision(PpmpConsolidationService $service): void
    {
        $this->validate(['remarks' => ['required', 'string', 'min:5']]);
        $service->returnForRevision($this->consolidation, Auth::user(), $this->remarks);
        $this->showReturnModal = false;
        $this->consolidation->refresh();
        $this->step = 2;
        session()->flash('status', 'Consolidation returned for revision.');
    }

    public function lock(PpmpConsolidationService $service): void
    {
        Gate::authorize('lock', $this->consolidation);
        $service->lock($this->consolidation, Auth::user());
        $this->consolidation->refresh();
        session()->flash('status', 'Final consolidated documents locked.');
    }

    public function cancel(PpmpConsolidationService $service): void
    {
        Gate::authorize('cancel', $this->consolidation);
        $this->validate(['remarks' => ['required', 'string', 'min:5']]);
        $service->cancel($this->consolidation, Auth::user(), $this->remarks);
        $this->showCancelModal = false;
        $this->consolidation->refresh();
        session()->flash('status', 'Consolidation cancelled. Source PPMPs are available for a new consolidation.');
        $this->redirect(route('ppmp-consolidations.index'), navigate: false);
    }

    public function goToStep(int $step): void
    {
        if ($this->consolidation && ! $this->consolidation->isEditable() && $step < 6) {
            return;
        }

        $this->step = max(1, min(7, $step));
    }

    public function render(PpmpConsolidationService $service)
    {
        $eligiblePpmps = collect();

        if ($this->fiscal_year_id && $this->document_type) {
            $eligiblePpmps = $service->eligiblePpmpsQuery(
                $this->fiscal_year_id,
                PpmpDocumentType::from($this->document_type),
                $this->consolidation?->id,
            )
                ->when($this->filter_division_id, fn ($q) => $q->where('division_id', $this->filter_division_id))
                ->when($this->search, fn ($q) => $q->where(function ($query) {
                    $query->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('control_no', 'like', '%'.$this->search.'%');
                }))
                ->get()
                ->map(function ($ppmp) {
                    $ppmp->display_total = $ppmp->items()->get()->sum(fn ($item) => $item->lineAbc());

                    return $ppmp;
                });
        }

        $warnings = $this->selectedPpmpIds
            ? $service->activeConsolidationWarnings($this->selectedPpmpIds, $this->consolidation?->id)
            : [];

        if ($this->consolidation) {
            $this->consolidation->load([
                'items.division', 'items.modeOfProcurement', 'items.fundSource', 'items.uacsCode',
                'bp2020Lines', 'wfpLines', 'sourcePpmps.division', 'fiscalYear', 'workflowHistories.performedBy',
            ]);
        }

        return view('livewire.planning.ppmp-consolidation-wizard', [
            'eligiblePpmps' => $eligiblePpmps,
            'warnings' => $warnings,
            'divisions' => Division::query()->orderBy('name')->get(),
            'offices' => Office::query()->when($this->filter_division_id, fn ($q) => $q->where('division_id', $this->filter_division_id))->orderBy('name')->get(),
            'fundSources' => FundSource::query()->orderBy('name')->get(),
            'fiscalYears' => FiscalYear::query()->orderByDesc('year')->get(),
            'documentTypes' => PpmpDocumentType::options(),
            'wizardSteps' => PpmpConsolidationStep::wizardSteps(),
        ])->layout('components.layouts.app', [
            'title' => $this->consolidation ? $this->consolidation->title : 'New PPMP Consolidation',
        ]);
    }
}
