<?php

namespace App\Livewire\Reports;

use App\Enums\ProcurementCaseStatus;
use App\Exports\GenericCollectionExport;
use App\Models\Bac\PhilgepsPosting;
use App\Models\Bac\Procurement as BacProcurement;
use App\Models\Budget\BudgetAllocation;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Planning\AnnualProcurementPlan;
use App\Models\Planning\Ppmp;
use App\Models\Procurement\NoticeOfAward;
use App\Models\Procurement\Payment;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Settings\FiscalYear;
use App\Models\Supplier\Bidder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

/**
 * Reports & Analytics hub. One tab per report family (Budget, PPMP/APP,
 * BAC/PhilGEPS, Suppliers/Awards, Purchase/Payment, Audit/COA,
 * Analytics); every tab can export its underlying dataset to Excel.
 */
#[Layout('components.layouts.app')]
class Index extends Component
{
    #[Url]
    public string $tab = 'overview';

    #[Url]
    public string $fiscalYearId = '';

    public function mount(): void
    {
        Gate::authorize('reports.view');
    }

    public function exportExcel(string $dataset)
    {
        Gate::authorize('reports.export');

        [$headings, $rows, $title] = $this->datasetFor($dataset);

        return Excel::download(new GenericCollectionExport($headings, collect($rows), $title), $title.'-'.now()->format('Ymd_His').'.xlsx');
    }

    protected function fiscalYear(): ?FiscalYear
    {
        if ($this->fiscalYearId) {
            return FiscalYear::find($this->fiscalYearId);
        }

        return FiscalYear::query()->where('is_current', true)->first() ?? FiscalYear::query()->orderByDesc('year')->first();
    }

    protected function datasetFor(string $dataset): array
    {
        return match ($dataset) {
            'budget-allocations' => [
                ['Department', 'Allocated', 'Utilized', 'Remaining'],
                BudgetAllocation::query()->whereNull('parent_id')->with('department')->get()->map(fn ($a) => [
                    $a->department?->name ?? '—', (float) $a->allocated_amount, (float) $a->utilized_amount, (float) $a->allocated_amount - (float) $a->utilized_amount,
                ]),
                'Budget Allocations',
            ],
            'ppmp' => [
                ['Control No.', 'Division', 'Fiscal Year', 'Status', 'Total ABC'],
                Ppmp::query()->with(['division', 'fiscalYear'])->get()->map(fn ($p) => [
                    $p->control_no ?? $p->id, $p->division?->name, $p->fiscalYear?->year, $p->status->label(), (float) $p->total_abc,
                ]),
                'PPMP Report',
            ],
            'philgeps-postings' => [
                ['Reference No.', 'Case No.', 'Posting Date', 'Closing Date', 'Status'],
                PhilgepsPosting::query()->with('procurement')->get()->map(fn ($p) => [
                    $p->reference_no, $p->procurement?->case_no, optional($p->posting_date)->format('Y-m-d'), optional($p->closing_date)->format('Y-m-d'), $p->status->label(),
                ]),
                'PhilGEPS Posting Report',
            ],
            'awards' => [
                ['NOA No.', 'Case No.', 'Supplier', 'Amount', 'Status', 'Issued'],
                NoticeOfAward::query()->with(['procurement', 'bidder'])->get()->map(fn ($n) => [
                    $n->noa_no, $n->procurement?->case_no, $n->bidder?->company_name, (float) $n->amount, $n->status->label(), optional($n->issued_at)->format('Y-m-d'),
                ]),
                'Award Report',
            ],
            'purchase-orders' => [
                ['PO No.', 'Supplier', 'Total Amount', 'Status', 'Delivery Date'],
                PurchaseOrder::query()->with('bidder')->get()->map(fn ($po) => [
                    $po->po_no, $po->bidder?->company_name, (float) $po->total_amount, $po->status->label(), optional($po->delivery_date)->format('Y-m-d'),
                ]),
                'Purchase Order Report',
            ],
            'payments' => [
                ['OR No.', 'PO No.', 'Amount', 'Method', 'Status', 'Date'],
                Payment::query()->with('purchaseOrder')->get()->map(fn ($p) => [
                    $p->or_no, $p->purchaseOrder?->po_no, (float) $p->amount, $p->method, $p->status, optional($p->payment_date)->format('Y-m-d'),
                ]),
                'Payment Monitoring Report',
            ],
            'audit-log' => [
                ['Date/Time', 'Module', 'Event', 'Description', 'Causer'],
                Activity::query()->latest()->limit(2000)->get()->map(fn ($a) => [
                    $a->created_at->format('Y-m-d H:i:s'), $a->log_name, $a->event, $a->description, $a->causer?->name ?? 'System',
                ]),
                'Audit Trail Report',
            ],
            default => [['—'], collect(), 'Report'],
        };
    }

    public function render()
    {
        $fy = $this->fiscalYear();

        $data = match ($this->tab) {
            'budget' => $this->budgetData($fy),
            'planning' => $this->planningData($fy),
            'bac' => $this->bacData(),
            'suppliers' => $this->suppliersData(),
            'purchases' => $this->purchasesData(),
            'audit' => $this->auditData(),
            'analytics' => $this->analyticsData(),
            default => $this->overviewData($fy),
        };

        return view('livewire.reports.index', array_merge(['fiscalYears' => FiscalYear::orderByDesc('year')->get(), 'fy' => $fy], $data))
            ->layout('components.layouts.app', ['title' => 'Reports & Analytics']);
    }

    protected function overviewData(?FiscalYear $fy): array
    {
        return [
            'totalGaa' => $fy ? GeneralAppropriationsAct::where('fiscal_year_id', $fy->id)->sum('total_amount') : 0,
            'totalUtilized' => $fy ? BudgetAllocation::where('fiscal_year_id', $fy->id)->whereNull('parent_id')->sum('utilized_amount') : 0,
            'prCount' => PurchaseRequest::count(),
            'activeCases' => BacProcurement::whereNotIn('status', [ProcurementCaseStatus::Completed, ProcurementCaseStatus::Cancelled])->count(),
            'awardsIssued' => NoticeOfAward::count(),
            'poTotalValue' => PurchaseOrder::sum('total_amount'),
            'paymentsProcessed' => Payment::sum('amount'),
            'caseStatusCounts' => BacProcurement::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
        ];
    }

    protected function budgetData(?FiscalYear $fy): array
    {
        $allocations = $fy ? BudgetAllocation::where('fiscal_year_id', $fy->id)->whereNull('parent_id')->with('department')->get() : collect();

        return [
            'gaa' => $fy ? GeneralAppropriationsAct::where('fiscal_year_id', $fy->id)->first() : null,
            'allocations' => $allocations,
            'totalAllocated' => $allocations->sum('allocated_amount'),
            'totalUtilized' => $allocations->sum('utilized_amount'),
        ];
    }

    protected function planningData(?FiscalYear $fy): array
    {
        return [
            'app' => $fy ? AnnualProcurementPlan::where('fiscal_year_id', $fy->id)->first() : null,
            'ppmpStatusCounts' => Ppmp::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'ppmps' => Ppmp::with(['division', 'fiscalYear'])->latest()->limit(15)->get(),
        ];
    }

    protected function bacData(): array
    {
        return [
            'caseStatusCounts' => BacProcurement::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'postings' => PhilgepsPosting::with('procurement')->latest('posting_date')->limit(15)->get(),
            'postingStatusCounts' => PhilgepsPosting::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
        ];
    }

    protected function suppliersData(): array
    {
        return [
            'bidderStatusCounts' => Bidder::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'topSuppliers' => NoticeOfAward::selectRaw('bidder_id, count(*) as awards, sum(amount) as total_amount')
                ->groupBy('bidder_id')->orderByDesc('total_amount')->with('bidder')->limit(10)->get(),
            'recentAwards' => NoticeOfAward::with(['procurement', 'bidder'])->latest()->limit(10)->get(),
        ];
    }

    protected function purchasesData(): array
    {
        return [
            'poStatusCounts' => PurchaseOrder::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'totalPoValue' => PurchaseOrder::sum('total_amount'),
            'totalPaid' => Payment::where('status', 'released')->sum('amount'),
            'totalProcessed' => Payment::sum('amount'),
            'recentPayments' => Payment::with('purchaseOrder')->latest()->limit(10)->get(),
        ];
    }

    protected function auditData(): array
    {
        return [
            'totalEvents' => Activity::count(),
            'eventsToday' => Activity::whereDate('created_at', today())->count(),
            'byModule' => Activity::selectRaw('log_name, count(*) as c')->groupBy('log_name')->orderByDesc('c')->pluck('c', 'log_name'),
            'recentActivity' => Activity::with('causer')->latest()->limit(15)->get(),
        ];
    }

    protected function analyticsData(): array
    {
        $completed = BacProcurement::with(['purchaseOrders' => fn ($q) => $q->whereNotNull('approved_at')])
            ->whereNotNull('created_at')
            ->get()
            ->filter(fn ($case) => $case->purchaseOrders->isNotEmpty());

        $cycleDays = $completed->map(function ($case) {
            $poApproved = $case->purchaseOrders->min('approved_at');

            return $poApproved ? $case->created_at->diffInDays($poApproved) : null;
        })->filter()->values();

        $savings = NoticeOfAward::with('procurement')->get()
            ->filter(fn ($n) => $n->procurement?->abc)
            ->map(fn ($n) => (float) $n->procurement->abc - (float) $n->amount);

        return [
            'avgCycleDays' => $cycleDays->isNotEmpty() ? round($cycleDays->avg(), 1) : null,
            'totalSavings' => $savings->sum(),
            'avgSavingsPct' => $savings->isNotEmpty() && NoticeOfAward::sum('amount') > 0
                ? round(($savings->sum() / NoticeOfAward::with('procurement')->get()->sum(fn ($n) => (float) ($n->procurement?->abc ?? 0))) * 100, 1)
                : null,
            'monthlyPrCounts' => PurchaseRequest::query()->get()->groupBy(fn ($pr) => $pr->created_at->format('Y-m'))->map->count()->sortKeys(),
            'topSuppliers' => NoticeOfAward::selectRaw('bidder_id, count(*) as awards, sum(amount) as total_amount')
                ->groupBy('bidder_id')->orderByDesc('total_amount')->with('bidder')->limit(5)->get(),
        ];
    }
}
