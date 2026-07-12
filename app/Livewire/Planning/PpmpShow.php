<?php

namespace App\Livewire\Planning;

use App\Exceptions\BudgetExceededException;
use App\Models\Budget\BudgetAllocation;
use App\Models\Planning\Ppmp;
use App\Models\Settings\ModeOfProcurement;
use App\Services\Planning\PpmpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PpmpShow extends Component
{
    public Ppmp $ppmp;

    public string $remarks = '';

    public bool $showReturnModal = false;

    /** @var array<int, array{item_id: string, mode_of_procurement_id: string}> */
    public array $itemModes = [];

    /** @var array<int, array{item_id: string, budget_allocation_id: string}> */
    public array $itemBudgets = [];

    public function mount(Ppmp $ppmp): void
    {
        Gate::authorize('view', $ppmp);
        $this->ppmp = $ppmp->load(['projectProposal', 'marketScoping']);
        $this->loadItemAssignments();
    }

    protected function loadItemAssignments(): void
    {
        $items = $this->ppmp->items()->orderBy('item_no')->get();

        $this->itemModes = $items->map(fn ($item) => [
            'item_id' => $item->id,
            'mode_of_procurement_id' => $item->mode_of_procurement_id ?? '',
        ])->all();

        $this->itemBudgets = $items->map(fn ($item) => [
            'item_id' => $item->id,
            'budget_allocation_id' => $item->budget_allocation_id ?? '',
        ])->all();
    }

    public function submit(PpmpService $service): void
    {
        Gate::authorize('submit', $this->ppmp);
        $service->submitForReview($this->ppmp, Auth::user());
        session()->flash('status', 'PPMP submitted for Division Chief review.');
        $this->ppmp->refresh();
    }

    public function divisionChiefApprove(PpmpService $service): void
    {
        Gate::authorize('divisionChiefReview', $this->ppmp);
        $service->divisionChiefApprove($this->ppmp, Auth::user(), $this->remarks);
        session()->flash('status', 'PPMP endorsed by Division Chief.');
        $this->ppmp->refresh();
    }

    public function planningApprove(PpmpService $service): void
    {
        Gate::authorize('planningReview', $this->ppmp);
        $service->planningApprove($this->ppmp, Auth::user(), $this->remarks);
        session()->flash('status', 'PPMP endorsed by Planning Office. Forwarded to BAC Secretariat for consolidation.');
        $this->ppmp->refresh();
    }

    public function bacConsolidate(PpmpService $service): void
    {
        Gate::authorize('bacConsolidate', $this->ppmp);
        $service->bacConsolidate($this->ppmp, Auth::user(), $this->remarks);
        session()->flash('status', 'PPMP consolidated by BAC Secretariat. Forwarded for procurement mode recommendation.');
        $this->ppmp->refresh();
    }

    public function recommendProcurementModes(PpmpService $service): void
    {
        Gate::authorize('procurementModeReview', $this->ppmp);

        try {
            $service->recommendProcurementModes($this->ppmp, Auth::user(), $this->itemModes, $this->remarks);
            session()->flash('status', 'BAC recommended mode of procurement. Forwarded to Budget Officer for budget linkage.');
        } catch (\Throwable $e) {
            $this->addError('procurement', $e->getMessage());
        }

        $this->ppmp->refresh();
        $this->loadItemAssignments();
    }

    public function applySuggestedModes(PpmpService $service): void
    {
        Gate::authorize('procurementModeReview', $this->ppmp);

        foreach ($service->suggestProcurementModes($this->ppmp) as $i => $suggestion) {
            if ($suggestion['suggested_mode_id']) {
                $this->itemModes[$i]['mode_of_procurement_id'] = $suggestion['suggested_mode_id'];
            }
        }
    }

    public function budgetOfficerApprove(PpmpService $service): void
    {
        Gate::authorize('budgetValidate', $this->ppmp);

        try {
            $service->linkBudgetAllocations($this->ppmp, $this->itemBudgets);
            $service->budgetOfficerApprove($this->ppmp, Auth::user(), $this->remarks);
            session()->flash('status', 'Budget linkage supported. PPMP approved pending final budget commitment.');
        } catch (\Throwable $e) {
            $this->addError('budget', $e->getMessage());
        }

        $this->ppmp->refresh();
        $this->loadItemAssignments();
    }

    public function approve(PpmpService $service): void
    {
        Gate::authorize('approve', $this->ppmp);

        try {
            $service->approve($this->ppmp, Auth::user(), $this->remarks);
            session()->flash('status', 'PPMP approved. Budget utilization has been committed.');
        } catch (BudgetExceededException $e) {
            $this->addError('budget', $e->getMessage());
        }

        $this->ppmp->refresh();
    }

    public function lock(PpmpService $service): void
    {
        Gate::authorize('lock', $this->ppmp);
        $service->lock($this->ppmp, Auth::user());
        session()->flash('status', 'PPMP locked for the fiscal year.');
        $this->ppmp->refresh()->load(['projectProposal', 'marketScoping']);
    }

    public function returnForRevision(PpmpService $service): void
    {
        $this->validate(['remarks' => ['required', 'string', 'min:5']]);
        $service->returnForRevision($this->ppmp, Auth::user(), $this->remarks);
        $this->showReturnModal = false;
        session()->flash('status', 'PPMP returned for revision.');
        $this->ppmp->refresh();
    }

    public function createRevision(string $type, PpmpService $service): void
    {
        $revision = $service->createRevision($this->ppmp, $type, Auth::user());
        $this->redirect(route('ppmps.edit', $revision), navigate: false);
    }

    public function render()
    {
        $items = $this->ppmp->items()->with(['modeOfProcurement', 'fundSource', 'pap', 'uacsCode', 'budgetAllocation'])->orderBy('item_no')->get();
        $history = $this->ppmp->workflowHistories()->with('performedBy')->latest('performed_at')->get();
        $modes = ModeOfProcurement::query()->where('is_active', true)->orderBy('sort_order')->get();
        $allocations = BudgetAllocation::query()
            ->when($this->ppmp->division_id, fn ($q) => $q->where('division_id', $this->ppmp->division_id))
            ->when($this->ppmp->fiscal_year_id, fn ($q) => $q->where('fiscal_year_id', $this->ppmp->fiscal_year_id))
            ->with(['pap', 'fundSource'])
            ->get();

        return view('livewire.planning.ppmp-show', compact('items', 'history', 'modes', 'allocations'))
            ->layout('components.layouts.app', ['title' => $this->ppmp->title]);
    }
}
