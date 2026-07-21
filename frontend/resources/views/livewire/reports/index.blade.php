<div>
    <x-page-header title="Reports & Analytics" subtitle="Budget, PPMP/APP, BAC, Supplier, Purchase, and Audit reports with Excel export.">
        <x-slot:actions>
            <select wire:model.live="fiscalYearId" class="rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="">Current Fiscal Year</option>
                @foreach($fiscalYears as $year)
                    <option value="{{ $year->id }}">{{ $year->year }}</option>
                @endforeach
            </select>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-4 flex flex-wrap gap-2 border-b border-slate-200 pb-3 dark:border-slate-800">
        @foreach([
            'overview' => 'Overview',
            'budget' => 'Budget',
            'planning' => 'PPMP & APP',
            'bac' => 'BAC & PhilGEPS',
            'suppliers' => 'Suppliers & Awards',
            'purchases' => 'Purchases & Payments',
            'audit' => 'Audit & COA',
            'analytics' => 'Analytics',
        ] as $key => $label)
            <button type="button" wire:click="$set('tab', '{{ $key }}')"
                    class="rounded-full px-3 py-1.5 text-xs font-medium transition
                        {{ $tab === $key ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if($tab === 'overview')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card label="Total GAA (FY)" :value="'₱'.number_format($totalGaa, 2)" icon="banknotes" />
            <x-stat-card label="Budget Utilized" :value="'₱'.number_format($totalUtilized, 2)" icon="chart-pie" accent="emerald" />
            <x-stat-card label="Purchase Requests" :value="$prCount" icon="document-text" accent="indigo" />
            <x-stat-card label="Active BAC Cases" :value="$activeCases" icon="briefcase" accent="amber" />
            <x-stat-card label="Awards Issued" :value="$awardsIssued" icon="check-badge" />
            <x-stat-card label="PO Total Value" :value="'₱'.number_format($poTotalValue, 2)" icon="truck" accent="indigo" />
            <x-stat-card label="Payments Processed" :value="'₱'.number_format($paymentsProcessed, 2)" icon="credit-card" accent="emerald" />
        </div>

        <x-card title="Procurement Case Status" class="mt-6">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                @foreach($caseStatusCounts as $status => $count)
                    <div class="rounded-lg bg-slate-50 p-3 text-center dark:bg-slate-800">
                        <p class="text-xl font-bold text-slate-800 dark:text-white">{{ $count }}</p>
                        <p class="text-xs text-slate-500">{{ \App\Enums\ProcurementCaseStatus::from($status)->label() }}</p>
                    </div>
                @endforeach
            </div>
        </x-card>
    @elseif($tab === 'budget')
        <div class="mb-4 flex justify-end">
            @can('reports.export')
                <x-button size="sm" variant="secondary" wire:click="exportExcel('budget-allocations')">Export to Excel</x-button>
            @endcan
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat-card label="GAA Total" :value="'₱'.number_format($gaa->total_amount ?? 0, 2)" icon="banknotes" />
            <x-stat-card label="Allocated" :value="'₱'.number_format($totalAllocated, 2)" icon="chart-pie" accent="indigo" />
            <x-stat-card label="Utilized" :value="'₱'.number_format($totalUtilized, 2)" icon="chart-bar" accent="emerald" />
        </div>
        <x-card title="Allocation by Department" class="mt-4">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead><tr class="text-left text-xs uppercase text-slate-400"><th class="py-2 pr-4">Department</th><th class="py-2 pr-4 text-right">Allocated</th><th class="py-2 pr-4 text-right">Utilized</th><th class="py-2 pr-4 text-right">Remaining</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($allocations as $a)
                        <tr>
                            <td class="py-2.5 pr-4">{{ $a->department?->name ?? '—' }}</td>
                            <td class="py-2.5 pr-4 text-right">₱{{ number_format($a->allocated_amount, 2) }}</td>
                            <td class="py-2.5 pr-4 text-right">₱{{ number_format($a->utilized_amount, 2) }}</td>
                            <td class="py-2.5 pr-4 text-right">₱{{ number_format($a->remaining_balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty-state icon="banknotes" title="No allocations for this fiscal year" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    @elseif($tab === 'planning')
        <div class="mb-4 flex justify-end">
            @can('reports.export')
                <x-button size="sm" variant="secondary" wire:click="exportExcel('ppmp')">Export PPMP to Excel</x-button>
            @endcan
        </div>
        <x-card title="APP Status">
            <p class="text-sm text-slate-600 dark:text-slate-300">{{ $app ? $app->status->label() : 'No Annual Procurement Plan for this fiscal year yet.' }}</p>
        </x-card>
        <x-card title="PPMP Status Distribution" class="mt-4">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach($ppmpStatusCounts as $status => $count)
                    <div class="rounded-lg bg-slate-50 p-3 text-center dark:bg-slate-800">
                        <p class="text-xl font-bold text-slate-800 dark:text-white">{{ $count }}</p>
                        <p class="text-xs text-slate-500">{{ \App\Enums\PpmpStatus::from($status)->label() }}</p>
                    </div>
                @endforeach
            </div>
        </x-card>
        <x-card title="Recent PPMPs" class="mt-4">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead><tr class="text-left text-xs uppercase text-slate-400"><th class="py-2 pr-4">Control No.</th><th class="py-2 pr-4">Division</th><th class="py-2 pr-4 text-right">Total ABC</th><th class="py-2 pr-4">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($ppmps as $p)
                        <tr>
                            <td class="py-2.5 pr-4 font-mono text-xs">{{ $p->control_no }}</td>
                            <td class="py-2.5 pr-4">{{ $p->division?->name }}</td>
                            <td class="py-2.5 pr-4 text-right">₱{{ number_format($p->total_abc, 2) }}</td>
                            <td class="py-2.5 pr-4"><x-status-badge :status="$p->status" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-card>
    @elseif($tab === 'bac')
        <div class="mb-4 flex justify-end">
            @can('reports.export')
                <x-button size="sm" variant="secondary" wire:click="exportExcel('philgeps-postings')">Generate Posting Report (Excel)</x-button>
            @endcan
        </div>
        <x-card title="Case Status Distribution">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                @foreach($caseStatusCounts as $status => $count)
                    <div class="rounded-lg bg-slate-50 p-3 text-center dark:bg-slate-800">
                        <p class="text-xl font-bold text-slate-800 dark:text-white">{{ $count }}</p>
                        <p class="text-xs text-slate-500">{{ \App\Enums\ProcurementCaseStatus::from($status)->label() }}</p>
                    </div>
                @endforeach
            </div>
        </x-card>
        <x-card title="PhilGEPS Postings" class="mt-4">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead><tr class="text-left text-xs uppercase text-slate-400"><th class="py-2 pr-4">Reference No.</th><th class="py-2 pr-4">Case</th><th class="py-2 pr-4">Closing Date</th><th class="py-2 pr-4">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($postings as $p)
                        <tr>
                            <td class="py-2.5 pr-4 font-mono text-xs">{{ $p->reference_no }}</td>
                            <td class="py-2.5 pr-4">{{ $p->procurement?->case_no }}</td>
                            <td class="py-2.5 pr-4">{{ $p->closing_date?->format('M d, Y') }}</td>
                            <td class="py-2.5 pr-4"><x-status-badge :status="$p->status" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-card>
    @elseif($tab === 'suppliers')
        <div class="mb-4 flex justify-end">
            @can('reports.export')
                <x-button size="sm" variant="secondary" wire:click="exportExcel('awards')">Export Awards to Excel</x-button>
            @endcan
        </div>
        <x-card title="Bidder Status">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach($bidderStatusCounts as $status => $count)
                    <div class="rounded-lg bg-slate-50 p-3 text-center dark:bg-slate-800">
                        <p class="text-xl font-bold text-slate-800 dark:text-white">{{ $count }}</p>
                        <p class="text-xs capitalize text-slate-500">{{ $status }}</p>
                    </div>
                @endforeach
            </div>
        </x-card>
        <x-card title="Top Suppliers by Award Value" class="mt-4">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead><tr class="text-left text-xs uppercase text-slate-400"><th class="py-2 pr-4">Supplier</th><th class="py-2 pr-4 text-right">Awards</th><th class="py-2 pr-4 text-right">Total Value</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($topSuppliers as $row)
                        <tr>
                            <td class="py-2.5 pr-4">{{ $row->bidder?->company_name ?? '—' }}</td>
                            <td class="py-2.5 pr-4 text-right">{{ $row->awards }}</td>
                            <td class="py-2.5 pr-4 text-right">₱{{ number_format($row->total_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-card>
    @elseif($tab === 'purchases')
        <div class="mb-4 flex justify-end gap-2">
            @can('reports.export')
                <x-button size="sm" variant="secondary" wire:click="exportExcel('purchase-orders')">Export POs</x-button>
                <x-button size="sm" variant="secondary" wire:click="exportExcel('payments')">Export Payments</x-button>
            @endcan
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat-card label="Total PO Value" :value="'₱'.number_format($totalPoValue, 2)" icon="truck" />
            <x-stat-card label="Total Processed" :value="'₱'.number_format($totalProcessed, 2)" icon="credit-card" accent="indigo" />
            <x-stat-card label="Total Paid (Released)" :value="'₱'.number_format($totalPaid, 2)" icon="banknotes" accent="emerald" />
        </div>
        <x-card title="Purchase Order Status" class="mt-4">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach($poStatusCounts as $status => $count)
                    <div class="rounded-lg bg-slate-50 p-3 text-center dark:bg-slate-800">
                        <p class="text-xl font-bold text-slate-800 dark:text-white">{{ $count }}</p>
                        <p class="text-xs text-slate-500">{{ \App\Enums\PurchaseOrderStatus::from($status)->label() }}</p>
                    </div>
                @endforeach
            </div>
        </x-card>
    @elseif($tab === 'audit')
        <div class="mb-4 flex justify-end">
            @can('reports.export')
                <x-button size="sm" variant="secondary" wire:click="exportExcel('audit-log')">Export Audit Log</x-button>
            @endcan
            <x-button href="{{ route('audit-trail.index') }}" size="sm" class="ml-2">Open Full Audit Trail</x-button>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-stat-card label="Total Audit Events" :value="number_format($totalEvents)" icon="shield-check" />
            <x-stat-card label="Events Today" :value="number_format($eventsToday)" icon="clipboard" accent="indigo" />
        </div>
        <x-card title="Events by Module" class="mt-4">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach($byModule as $module => $count)
                    <div class="rounded-lg bg-slate-50 p-3 text-center dark:bg-slate-800">
                        <p class="text-xl font-bold text-slate-800 dark:text-white">{{ $count }}</p>
                        <p class="text-xs capitalize text-slate-500">{{ str_replace('_', ' ', $module) }}</p>
                    </div>
                @endforeach
            </div>
        </x-card>
    @elseif($tab === 'analytics')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat-card label="Avg. Cycle Time" :value="$avgCycleDays !== null ? $avgCycleDays.' days' : '—'" icon="clipboard" sub="Case creation → PO approval" />
            <x-stat-card label="Total Savings (ABC − Awarded)" :value="'₱'.number_format($totalSavings, 2)" icon="banknotes" accent="emerald" />
            <x-stat-card label="Avg. Savings Rate" :value="$avgSavingsPct !== null ? $avgSavingsPct.'%' : '—'" icon="chart-pie" accent="indigo" />
        </div>
        <x-card title="Monthly Purchase Requests" class="mt-4">
            <div class="flex items-end gap-2 overflow-x-auto pb-2" style="height: 120px;">
                @forelse($monthlyPrCounts as $month => $count)
                    @php $max = max($monthlyPrCounts->max(), 1); @endphp
                    <div class="flex flex-col items-center justify-end" style="height: 100%;">
                        <div class="w-8 rounded-t bg-primary-500" style="height: {{ max(($count / $max) * 100, 4) }}%"></div>
                        <span class="mt-1 text-[10px] text-slate-400">{{ $month }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No data yet.</p>
                @endforelse
            </div>
        </x-card>
        <x-card title="Top Suppliers" class="mt-4">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($topSuppliers as $row)
                        <tr><td class="py-2 pr-4">{{ $row->bidder?->company_name ?? '—' }}</td><td class="py-2 pr-4 text-right">{{ $row->awards }} award(s)</td><td class="py-2 pr-4 text-right">₱{{ number_format($row->total_amount, 2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </x-card>
    @endif
</div>
