<?php

namespace App\Livewire\Dashboard;

use App\Enums\CafStatus;
use App\Enums\PhilgepsPostingStatus;
use App\Enums\PpmpStatus;
use App\Enums\ProcurementCaseStatus;
use App\Enums\PurchaseRequestStatus;
use App\Models\Bac\BacCalendarEvent;
use App\Models\Bac\BidEvaluation;
use App\Models\Bac\PhilgepsPosting;
use App\Models\Bac\Procurement as BacProcurement;
use App\Models\Budget\BudgetAllocation;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Planning\AnnualProcurementPlan;
use App\Models\Planning\Ppmp;
use App\Models\Procurement\CertificateOfAvailabilityOfFunds;
use App\Models\Procurement\NoticeOfAward;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Settings\FiscalYear;
use App\Models\Supplier\Bidder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public string $type = 'executive';

    public function mount(string $type = 'executive'): void
    {
        $this->type = $type;
    }

    public function render()
    {
        $fiscalYear = FiscalYear::query()->where('is_current', true)->first()
            ?? FiscalYear::query()->orderByDesc('year')->first();

        $data = match ($this->type) {
            'executive' => $this->executiveData($fiscalYear),
            'budget' => $this->budgetData($fiscalYear),
            'bac' => $this->bacData(),
            'planning' => $this->planningData($fiscalYear),
            'division' => $this->divisionData($fiscalYear),
            'supplier' => $this->supplierData(),
            'analytics' => $this->analyticsData($fiscalYear),
            default => [],
        };

        return view('livewire.dashboard.show', array_merge(['fiscalYear' => $fiscalYear], $data))
            ->layout('components.layouts.app', ['title' => ucfirst($this->type).' Dashboard']);
    }

    protected function executiveData(?FiscalYear $fy): array
    {
        return [
            'totalGaa' => $fy ? GeneralAppropriationsAct::query()->where('fiscal_year_id', $fy->id)->sum('total_amount') : 0,
            'totalUtilized' => $fy ? BudgetAllocation::query()->where('fiscal_year_id', $fy->id)->whereNull('parent_id')->sum('utilized_amount') : 0,
            'appStatusCounts' => $fy ? AnnualProcurementPlan::query()->where('fiscal_year_id', $fy->id)->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status') : collect(),
            'ppmpStatusCounts' => Ppmp::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'prStatusCounts' => PurchaseRequest::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'caseStatusCounts' => BacProcurement::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'upcomingEvents' => BacCalendarEvent::query()->with('procurement')->where('scheduled_at', '>=', now())->orderBy('scheduled_at')->limit(5)->get(),
            'recentAwards' => NoticeOfAward::query()->with(['procurement', 'bidder'])->latest()->limit(5)->get(),
            'monthlyPr' => PurchaseRequest::query()->selectRaw("strftime('%m', created_at) as m, count(*) as c")->groupBy('m')->pluck('c', 'm'),
        ];
    }

    protected function budgetData(?FiscalYear $fy): array
    {
        $allocations = $fy ? BudgetAllocation::query()->where('fiscal_year_id', $fy->id)->whereNull('parent_id')->with('department')->get() : collect();

        return [
            'gaa' => $fy ? GeneralAppropriationsAct::query()->where('fiscal_year_id', $fy->id)->first() : null,
            'allocations' => $allocations,
            'totalAllocated' => $allocations->sum('allocated_amount'),
            'totalUtilized' => $allocations->sum('utilized_amount'),
            'pendingCaf' => CertificateOfAvailabilityOfFunds::query()->where('status', '!=', CafStatus::Printed)->count(),
            'pendingBudgetReviewPr' => PurchaseRequest::query()->where('status', PurchaseRequestStatus::Budget)->count(),
        ];
    }

    protected function bacData(): array
    {
        return [
            'caseStatusCounts' => BacProcurement::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'upcomingEvents' => BacCalendarEvent::query()->with('procurement')->where('scheduled_at', '>=', now())->orderBy('scheduled_at')->limit(8)->get(),
            'activeCases' => BacProcurement::query()->with('purchaseRequest')->whereNotIn('status', [ProcurementCaseStatus::Completed, ProcurementCaseStatus::Cancelled])->latest()->limit(8)->get(),
            'pendingEvaluations' => BidEvaluation::query()->count(),
        ];
    }

    protected function planningData(?FiscalYear $fy): array
    {
        return [
            'app' => $fy ? AnnualProcurementPlan::query()->where('fiscal_year_id', $fy->id)->first() : null,
            'ppmpStatusCounts' => Ppmp::query()->when($fy, fn ($q) => $q->where('fiscal_year_id', $fy->id))->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'ppmpsForReview' => Ppmp::query()->with('division')->where('status', PpmpStatus::PlanningReview)->limit(8)->get(),
        ];
    }

    protected function divisionData(?FiscalYear $fy): array
    {
        $user = Auth::user();
        $divisionId = $user?->division_id;

        return [
            'ppmps' => Ppmp::query()->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))->latest()->limit(8)->get(),
            'purchaseRequests' => PurchaseRequest::query()->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))->latest()->limit(8)->get(),
            'prStatusCounts' => PurchaseRequest::query()->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
        ];
    }

    protected function supplierData(): array
    {
        return [
            'totalBidders' => Bidder::query()->count(),
            'verifiedBidders' => Bidder::query()->where('status', 'verified')->count(),
            'openPostings' => PhilgepsPosting::query()->where('status', PhilgepsPostingStatus::Published)->count(),
            'recentBidders' => Bidder::query()->latest()->limit(8)->get(),
        ];
    }

    protected function analyticsData(?FiscalYear $fy): array
    {
        return [
            'totalGaa' => $fy ? GeneralAppropriationsAct::query()->where('fiscal_year_id', $fy->id)->sum('total_amount') : 0,
            'monthlyPr' => PurchaseRequest::query()->selectRaw("strftime('%m', created_at) as m, count(*) as c")->groupBy('m')->pluck('c', 'm'),
            'caseStatusCounts' => BacProcurement::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'topSuppliers' => NoticeOfAward::query()->selectRaw('bidder_id, count(*) as awards')->groupBy('bidder_id')->orderByDesc('awards')->with('bidder')->limit(5)->get(),
            'auditEventsToday' => Activity::query()->whereDate('created_at', today())->count(),
        ];
    }
}
