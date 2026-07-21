<div>
    <x-page-header title="Annual Procurement Plan" :subtitle="$fiscalYear ? 'Fiscal Year '.$fiscalYear->year : 'No fiscal year selected'">
        <x-slot:actions>
            <select onchange="window.location = this.value" class="rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                @foreach($fiscalYears as $fy)
                    <option value="{{ route('app.show', $fy) }}" @selected($fiscalYear?->id === $fy->id)>FY {{ $fy->year }}</option>
                @endforeach
            </select>
        </x-slot:actions>
    </x-page-header>

    @if(!$app)
        <x-empty-state icon="clipboard" title="No APP yet for this fiscal year" description="The Annual Procurement Plan is automatically created once the GAA for this fiscal year is approved." />
    @else
        <x-page-header :title="$app->reference_no" subtitle="">
            <x-slot:actions>
                <x-status-badge :status="$app->status" class="!text-sm" />
                @can('consolidate', $app)
                    @if($app->status === \App\Enums\AnnualProcurementPlanStatus::Draft)
                        <x-button wire:click="consolidate">Consolidate PPMPs</x-button>
                    @endif
                @endcan
                @can('review', $app)
                    @if($app->status === \App\Enums\AnnualProcurementPlanStatus::ForConsolidation)
                        <x-button wire:click="sendToBacReview">Send to BAC Review</x-button>
                    @endif
                @endcan
                @can('approve', $app)
                    @if($app->status === \App\Enums\AnnualProcurementPlanStatus::BacReview)
                        <x-button wire:click="approve" variant="success">Approve</x-button>
                    @endif
                @endcan
                @can('lock', $app)
                    @if($app->status === \App\Enums\AnnualProcurementPlanStatus::Approved)
                        <x-button wire:click="lock" wire:confirm="Lock this APP? No further PPMP changes will be accepted until it is unlocked." variant="secondary">Lock APP</x-button>
                    @endif
                @endcan
                @can('unlock', $app)
                    @if($app->status === \App\Enums\AnnualProcurementPlanStatus::Locked)
                        <x-button wire:click="unlock" wire:confirm="Unlock this APP? Division PPMP updates may resume." variant="secondary">Unlock APP</x-button>
                    @endif
                @endcan
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat-card label="Total Budget (from GAA)" :value="'₱'.number_format($app->total_budget, 2)" icon="banknotes" />
            <x-stat-card label="Total Planned (Approved PPMPs)" :value="'₱'.number_format($app->total_planned_amount, 2)" icon="clipboard" accent="indigo" />
            <x-stat-card label="Remaining" :value="'₱'.number_format($app->remainingBudget(), 2)" icon="chart-pie" accent="emerald" />
        </div>

        @if($app->isLocked() && $app->locked_at)
            <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-200">
                Locked on {{ $app->locked_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}.
                Use <strong>Unlock APP</strong> to allow PPMP updates again.
            </div>
        @endif

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <x-card title="Division PPMPs" class="lg:col-span-2">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead>
                            <tr class="text-left text-xs uppercase text-slate-400">
                                <th class="py-2 pr-4">Division</th>
                                <th class="py-2 pr-4">Title</th>
                                <th class="py-2 pr-4 text-right">Total ABC</th>
                                <th class="py-2 pr-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($ppmps as $ppmp)
                                <tr>
                                    <td class="py-2.5 pr-4">{{ $ppmp->division?->name }}</td>
                                    <td class="py-2.5 pr-4"><a href="{{ route('ppmps.show', $ppmp) }}" class="text-primary-700 hover:underline dark:text-primary-400">{{ $ppmp->title }}</a></td>
                                    <td class="py-2.5 pr-4 text-right">₱{{ number_format($ppmp->total_abc, 2) }}</td>
                                    <td class="py-2.5 pr-4"><x-status-badge :status="$ppmp->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-6 text-center text-slate-400">No division PPMPs yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
            <x-card title="Workflow History">
                <x-workflow-timeline :history="$history" />
            </x-card>
        </div>
    @endif
</div>
