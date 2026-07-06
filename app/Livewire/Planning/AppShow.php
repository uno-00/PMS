<?php

namespace App\Livewire\Planning;

use App\Models\Planning\AnnualProcurementPlan;
use App\Models\Settings\FiscalYear;
use App\Services\Planning\AnnualProcurementPlanService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AppShow extends Component
{
    public ?AnnualProcurementPlan $app = null;

    public ?FiscalYear $fiscalYear = null;

    public function mount(?FiscalYear $fiscalYear = null): void
    {
        Gate::authorize('viewAny', AnnualProcurementPlan::class);

        $this->fiscalYear = $fiscalYear ?? FiscalYear::query()->where('is_current', true)->first() ?? FiscalYear::query()->orderByDesc('year')->first();
        $this->app = $this->fiscalYear ? AnnualProcurementPlan::query()->where('fiscal_year_id', $this->fiscalYear->id)->first() : null;
    }

    public function consolidate(AnnualProcurementPlanService $service): void
    {
        Gate::authorize('consolidate', $this->app);
        $service->consolidate($this->app, Auth::user());
        session()->flash('status', 'APP consolidated from division PPMPs.');
        $this->app->refresh();
    }

    public function sendToBacReview(AnnualProcurementPlanService $service): void
    {
        Gate::authorize('review', $this->app);
        $service->sendToBacReview($this->app, Auth::user());
        session()->flash('status', 'APP forwarded to BAC for review.');
        $this->app->refresh();
    }

    public function approve(AnnualProcurementPlanService $service): void
    {
        Gate::authorize('approve', $this->app);
        $service->approve($this->app, Auth::user());
        session()->flash('status', 'APP approved.');
        $this->app->refresh();
    }

    public function lock(AnnualProcurementPlanService $service): void
    {
        Gate::authorize('lock', $this->app);
        $service->lock($this->app, Auth::user());
        session()->flash('status', 'APP locked for the fiscal year.');
        $this->app->refresh();
    }

    public function unlock(AnnualProcurementPlanService $service): void
    {
        Gate::authorize('unlock', $this->app);
        $service->unlock($this->app, Auth::user());
        session()->flash('status', 'APP unlocked. You may resume PPMP updates before re-locking.');
        $this->app->refresh();
    }

    public function render()
    {
        $fiscalYears = FiscalYear::query()->orderByDesc('year')->get();
        $ppmps = $this->app ? $this->app->ppmps()->with('division')->get() : collect();
        $history = $this->app ? $this->app->workflowHistories()->with('performedBy')->latest('performed_at')->get() : collect();

        return view('livewire.planning.app-show', compact('fiscalYears', 'ppmps', 'history'))
            ->layout('components.layouts.app', ['title' => 'Annual Procurement Plan']);
    }
}
