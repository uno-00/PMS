<div>
    <x-page-header :title="ucfirst($type).' Dashboard'" :subtitle="$fiscalYear ? 'Fiscal Year '.$fiscalYear->year : 'No active fiscal year configured'" />

    @if($type === 'executive')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card label="Total GAA Budget" :value="'₱'.number_format($totalGaa, 2)" icon="banknotes" accent="primary" />
            <x-stat-card label="Budget Utilized" :value="'₱'.number_format($totalUtilized, 2)" icon="chart-pie" accent="emerald"
                         :sub="$totalGaa > 0 ? number_format(($totalUtilized/$totalGaa)*100, 1).'% of total budget' : null" />
            <x-stat-card label="Active Procurement Cases" :value="$caseStatusCounts->sum()" icon="briefcase" accent="indigo" />
            <x-stat-card label="Purchase Requests" :value="$prStatusCounts->sum()" icon="shopping-cart" accent="amber" />
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <x-card title="Monthly Purchase Requests" class="lg:col-span-2">
                <canvas x-data x-init="new Chart($el, {
                    type: 'bar',
                    data: {
                        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                        datasets: [{ label: 'Purchase Requests', backgroundColor: '#1d4ed8', data: {{ Js::from(collect(range(1,12))->map(fn($m) => $monthlyPr[str_pad($m,2,'0',STR_PAD_LEFT)] ?? 0)) }} }]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } } }
                })" height="220"></canvas>
            </x-card>

            <x-card title="Procurement Case Status">
                <canvas x-data x-init="new Chart($el, {
                    type: 'doughnut',
                    data: {
                        labels: {{ Js::from($caseStatusCounts->keys()->map(fn($s) => \App\Enums\ProcurementCaseStatus::from($s)->label())) }},
                        datasets: [{ data: {{ Js::from($caseStatusCounts->values()) }}, backgroundColor: ['#94a3b8','#f59e0b','#6366f1','#a855f7','#06b6d4','#10b981','#3b82f6','#0ea5e9','#22c55e','#ef4444'] }]
                    },
                    options: { responsive: true }
                })" height="220"></canvas>
            </x-card>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-card title="Upcoming BAC Activities">
                @forelse($upcomingEvents as $event)
                    <div class="flex items-center justify-between border-b border-slate-100 py-2.5 last:border-0 dark:border-slate-800">
                        <div>
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $event->typeLabel() }}</p>
                            <p class="text-xs text-slate-400">{{ $event->procurement?->title }}</p>
                        </div>
                        <span class="text-xs font-medium text-slate-500">{{ $event->scheduled_at->format('M d, Y g:ia') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No upcoming BAC activities scheduled.</p>
                @endforelse
            </x-card>

            <x-card title="Recent Awards">
                @forelse($recentAwards as $award)
                    <div class="flex items-center justify-between border-b border-slate-100 py-2.5 last:border-0 dark:border-slate-800">
                        <div>
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $award->noa_no }}</p>
                            <p class="text-xs text-slate-400">{{ $award->bidder?->company_name }}</p>
                        </div>
                        <x-status-badge :status="$award->status" />
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No awards issued yet.</p>
                @endforelse
            </x-card>
        </div>

    @elseif($type === 'budget')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card label="GAA Status" :value="$gaa?->status->label() ?? 'No GAA'" icon="banknotes" />
            <x-stat-card label="Total Allocated" :value="'₱'.number_format($totalAllocated, 2)" icon="chart-pie" accent="indigo" />
            <x-stat-card label="Total Utilized" :value="'₱'.number_format($totalUtilized, 2)" icon="credit-card" accent="amber" />
            <x-stat-card label="Pending CAF Actions" :value="$pendingCaf" icon="check-badge" accent="emerald" />
        </div>

        <x-card title="Budget Allocation by Department" class="mt-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">Department</th>
                            <th class="py-2 pr-4">Allocated</th>
                            <th class="py-2 pr-4">Utilized</th>
                            <th class="py-2 pr-4">Remaining</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($allocations as $alloc)
                            <tr>
                                <td class="py-2.5 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ $alloc->department?->name ?? '—' }}</td>
                                <td class="py-2.5 pr-4">₱{{ number_format($alloc->allocated_amount, 2) }}</td>
                                <td class="py-2.5 pr-4">₱{{ number_format($alloc->utilized_amount, 2) }}</td>
                                <td class="py-2.5 pr-4 font-medium text-emerald-600">₱{{ number_format($alloc->remaining_balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-slate-400">No budget allocations yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

    @elseif($type === 'bac')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card label="Active Cases" :value="$caseStatusCounts->sum()" icon="briefcase" />
            <x-stat-card label="Upcoming Activities" :value="$upcomingEvents->count()" icon="calendar" accent="amber" />
            <x-stat-card label="Bid Evaluations Logged" :value="$pendingEvaluations" icon="document-text" accent="indigo" />
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-card title="Active Procurement Cases">
                @forelse($activeCases as $case)
                    <a href="{{ route('procurements.show', $case) }}" class="flex items-center justify-between border-b border-slate-100 py-2.5 last:border-0 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50">
                        <div>
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $case->case_no }}</p>
                            <p class="text-xs text-slate-400">{{ $case->title }}</p>
                        </div>
                        <x-status-badge :status="$case->status" />
                    </a>
                @empty
                    <p class="text-sm text-slate-400">No active procurement cases.</p>
                @endforelse
            </x-card>
            <x-card title="Upcoming BAC Activities">
                @forelse($upcomingEvents as $event)
                    <div class="flex items-center justify-between border-b border-slate-100 py-2.5 last:border-0 dark:border-slate-800">
                        <div>
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $event->typeLabel() }}</p>
                            <p class="text-xs text-slate-400">{{ $event->procurement?->title }}</p>
                        </div>
                        <span class="text-xs font-medium text-slate-500">{{ $event->scheduled_at->format('M d, g:ia') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No upcoming activities.</p>
                @endforelse
            </x-card>
        </div>

    @elseif($type === 'planning')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card label="APP Status" :value="$app?->status->label() ?? 'Not created'" icon="clipboard" />
            <x-stat-card label="APP Total Budget" :value="'₱'.number_format($app?->total_budget ?? 0, 2)" icon="banknotes" accent="indigo" />
            <x-stat-card label="PPMPs Pending Planning Review" :value="$ppmpsForReview->count()" icon="document-text" accent="amber" />
        </div>

        <x-card title="PPMP Awaiting Planning Review" class="mt-6">
            @forelse($ppmpsForReview as $ppmp)
                <a href="{{ route('ppmps.show', $ppmp) }}" class="flex items-center justify-between border-b border-slate-100 py-2.5 last:border-0 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50">
                    <div>
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $ppmp->title }}</p>
                        <p class="text-xs text-slate-400">{{ $ppmp->division?->name }}</p>
                    </div>
                    <x-status-badge :status="$ppmp->status" />
                </a>
            @empty
                <p class="text-sm text-slate-400">Nothing pending review.</p>
            @endforelse
        </x-card>

    @elseif($type === 'division')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-stat-card label="My PPMPs" :value="$ppmps->count()" icon="document-text" />
            <x-stat-card label="My Purchase Requests" :value="$purchaseRequests->count()" icon="shopping-cart" accent="indigo" />
            <x-stat-card label="Pending Approvals" :value="$prStatusCounts->except(['approved','rejected','cancelled'])->sum()" icon="clipboard" accent="amber" />
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-card title="Recent PPMPs">
                @forelse($ppmps as $ppmp)
                    <a href="{{ route('ppmps.show', $ppmp) }}" class="flex items-center justify-between border-b border-slate-100 py-2.5 last:border-0 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50">
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $ppmp->title }}</p>
                        <x-status-badge :status="$ppmp->status" />
                    </a>
                @empty
                    <p class="text-sm text-slate-400">No PPMPs yet.</p>
                @endforelse
            </x-card>
            <x-card title="Recent Purchase Requests">
                @forelse($purchaseRequests as $pr)
                    <a href="{{ route('purchase-requests.show', $pr) }}" class="flex items-center justify-between border-b border-slate-100 py-2.5 last:border-0 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50">
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $pr->pr_no ?? $pr->purpose }}</p>
                        <x-status-badge :status="$pr->status" />
                    </a>
                @empty
                    <p class="text-sm text-slate-400">No purchase requests yet.</p>
                @endforelse
            </x-card>
        </div>

    @elseif($type === 'supplier')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card label="Registered Bidders" :value="$totalBidders" icon="building-office" />
            <x-stat-card label="Verified Bidders" :value="$verifiedBidders" icon="check-badge" accent="emerald" />
            <x-stat-card label="Open PhilGEPS Postings" :value="$openPostings" icon="globe" accent="indigo" />
        </div>

        <x-card title="Recently Registered Suppliers" class="mt-6">
            @forelse($recentBidders as $bidder)
                <div class="flex items-center justify-between border-b border-slate-100 py-2.5 last:border-0 dark:border-slate-800">
                    <div>
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $bidder->company_name }}</p>
                        <p class="text-xs text-slate-400">{{ $bidder->email }}</p>
                    </div>
                    <x-status-badge :status="$bidder->status" />
                </div>
            @empty
                <p class="text-sm text-slate-400">No suppliers registered yet.</p>
            @endforelse
        </x-card>

    @elseif($type === 'analytics')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card label="Total GAA Budget" :value="'₱'.number_format($totalGaa, 2)" icon="banknotes" />
            <x-stat-card label="Procurement Cases" :value="$caseStatusCounts->sum()" icon="briefcase" accent="indigo" />
            <x-stat-card label="Audit Events Today" :value="$auditEventsToday" icon="shield-check" accent="amber" />
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-card title="Monthly Purchase Request Volume">
                <canvas x-data x-init="new Chart($el, {
                    type: 'line',
                    data: {
                        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                        datasets: [{ label: 'PRs', borderColor: '#1d4ed8', backgroundColor: 'rgba(29,78,216,0.1)', fill: true, tension: 0.35, data: {{ Js::from(collect(range(1,12))->map(fn($m) => $monthlyPr[str_pad($m,2,'0',STR_PAD_LEFT)] ?? 0)) }} }]
                    },
                    options: { responsive: true }
                })" height="220"></canvas>
            </x-card>
            <x-card title="Top Suppliers by Awards">
                @forelse($topSuppliers as $row)
                    <div class="flex items-center justify-between border-b border-slate-100 py-2.5 last:border-0 dark:border-slate-800">
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $row->bidder?->company_name ?? 'Unknown' }}</p>
                        <span class="text-sm font-semibold text-primary-700 dark:text-primary-300">{{ $row->awards }} awards</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No award data yet.</p>
                @endforelse
            </x-card>
        </div>
    @endif
</div>
