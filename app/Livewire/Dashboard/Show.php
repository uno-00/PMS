<?php

namespace App\Livewire\Dashboard;

use App\Enums\CafStatus;
use App\Enums\PhilgepsPostingStatus;
use App\Enums\PpmpStatus;
use App\Enums\ProcurementCaseStatus;
use App\Models\Bac\BacCalendarEvent;
use App\Models\Bac\BidEvaluation;
use App\Models\Bac\PhilgepsPosting;
use App\Models\Bac\Procurement as BacProcurement;
use App\Models\Budget\BudgetAllocation;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Planning\AnnualProcurementPlan;
use App\Models\Planning\Ppmp;
use App\Models\Planning\PpmpItem;
use App\Models\Procurement\CertificateOfAvailabilityOfFunds;
use App\Models\Procurement\NoticeOfAward;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Settings\FiscalYear;
use App\Models\Supplier\Bidder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

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
        $spend = $this->monthlySpendSeries($fy);
        $caseStatusCounts = BacProcurement::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return array_merge($spend, [
            'totalGaa' => $fy ? GeneralAppropriationsAct::query()->where('fiscal_year_id', $fy->id)->sum('total_amount') : 0,
            'totalUtilized' => $fy ? BudgetAllocation::query()->where('fiscal_year_id', $fy->id)->whereNull('parent_id')->sum('utilized_amount') : 0,
            'prStatusCounts' => PurchaseRequest::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'caseStatusCounts' => $caseStatusCounts,
            'caseStatusChartItems' => $this->procurementStatusChartItems($caseStatusCounts),
            'upcomingEvents' => BacCalendarEvent::query()->with('procurement')->where('scheduled_at', '>=', now())->orderBy('scheduled_at')->limit(5)->get(),
            'recentAwards' => NoticeOfAward::query()->with(['procurement', 'bidder'])->latest()->limit(5)->get(),
        ]);
    }

    protected function budgetData(?FiscalYear $fy): array
    {
        $allocations = $fy ? BudgetAllocation::query()->where('fiscal_year_id', $fy->id)->whereNull('parent_id')->with('department')->get() : collect();

        return array_merge($this->monthlySpendSeries($fy), [
            'gaa' => $fy ? GeneralAppropriationsAct::query()->where('fiscal_year_id', $fy->id)->first() : null,
            'allocations' => $allocations,
            'totalAllocated' => $allocations->sum('allocated_amount'),
            'totalUtilized' => $allocations->sum('utilized_amount'),
            'pendingCaf' => CertificateOfAvailabilityOfFunds::query()->where('status', '!=', CafStatus::Printed)->count(),
        ]);
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
        return array_merge($this->monthlySpendSeries($fy), [
            'totalGaa' => $fy ? GeneralAppropriationsAct::query()->where('fiscal_year_id', $fy->id)->sum('total_amount') : 0,
            'monthlyPr' => PurchaseRequest::query()->selectRaw("strftime('%m', created_at) as m, count(*) as c")->groupBy('m')->pluck('c', 'm'),
            'caseStatusCounts' => BacProcurement::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'topSuppliers' => NoticeOfAward::query()->selectRaw('bidder_id, count(*) as awards')->groupBy('bidder_id')->orderByDesc('awards')->with('bidder')->limit(5)->get(),
            'auditEventsToday' => Activity::query()->whereDate('created_at', today())->count(),
        ]);
    }

    /** @return array{monthlyPlannedSpend: array<int, float>, monthlyActualSpend: array<int, float>, spendOnTrack: bool|null} */
    protected function monthlySpendSeries(?FiscalYear $fy): array
    {
        $planned = collect(range(1, 12))->map(function (int $month) use ($fy): float {
            if (! $fy) {
                return 0.0;
            }

            $key = str_pad((string) $month, 2, '0', STR_PAD_LEFT);

            $amount = PpmpItem::query()
                ->whereHas('ppmp', fn ($q) => $q->where('fiscal_year_id', $fy->id))
                ->whereNotNull('schedule_start')
                ->whereRaw("strftime('%m', schedule_start) = ?", [$key])
                ->sum('abc');

            return round((float) $amount / 1_000_000, 2);
        });

        $actual = collect(range(1, 12))->map(function (int $month) use ($fy): float {
            if (! $fy) {
                return 0.0;
            }

            $key = str_pad((string) $month, 2, '0', STR_PAD_LEFT);

            $amount = PurchaseRequest::query()
                ->where('fiscal_year_id', $fy->id)
                ->whereRaw("strftime('%m', created_at) = ?", [$key])
                ->sum('total_amount');

            return round((float) $amount / 1_000_000, 2);
        });

        $currentMonth = (int) now()->format('n');
        $plannedYtd = $planned->take($currentMonth)->sum();
        $actualYtd = $actual->take($currentMonth)->sum();

        $onTrack = null;
        if ($plannedYtd > 0) {
            $onTrack = $actualYtd <= ($plannedYtd * 1.05);
        }

        return [
            'monthlyPlannedSpend' => $planned->values()->all(),
            'monthlyActualSpend' => $actual->values()->all(),
            'spendOnTrack' => $onTrack,
        ];
    }

    /** @return array<int, array{label: string, value: int, color: string}> */
    protected function procurementStatusChartItems($counts): array
    {
        return collect($counts)
            ->map(function ($count, $status) {
                $enum = ProcurementCaseStatus::from($status);

                return [
                    'label' => $enum->label(),
                    'value' => (int) $count,
                    'color' => match ($enum->color()) {
                        'slate' => '#64748b',
                        'amber' => '#d97706',
                        'indigo' => '#4f46e5',
                        'emerald' => '#059669',
                        'red' => '#dc2626',
                        default => '#94a3b8',
                    },
                ];
            })
            ->sortByDesc('value')
            ->values()
            ->all();
    }
}
