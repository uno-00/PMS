<?php

namespace App\Livewire\Planning;

use App\Exceptions\BudgetExceededException;
use App\Models\Planning\Ppmp;
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

    public function mount(Ppmp $ppmp): void
    {
        Gate::authorize('view', $ppmp);
        $this->ppmp = $ppmp;
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
        session()->flash('status', 'PPMP endorsed by Planning Office.');
        $this->ppmp->refresh();
    }

    public function budgetOfficerApprove(PpmpService $service): void
    {
        Gate::authorize('budgetValidate', $this->ppmp);

        try {
            $service->budgetOfficerApprove($this->ppmp, Auth::user(), $this->remarks);
            session()->flash('status', 'Budget validated. Forwarded for BAC consolidation.');
        } catch (\Throwable $e) {
            $this->addError('budget', $e->getMessage());
        }

        $this->ppmp->refresh();
    }

    public function bacConsolidate(PpmpService $service): void
    {
        Gate::authorize('bacConsolidate', $this->ppmp);
        $service->bacConsolidate($this->ppmp, Auth::user(), $this->remarks);
        session()->flash('status', 'PPMP consolidated by BAC.');
        $this->ppmp->refresh();
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
        $this->ppmp->refresh();
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

        return view('livewire.planning.ppmp-show', compact('items', 'history'))
            ->layout('components.layouts.app', ['title' => $this->ppmp->title]);
    }
}
