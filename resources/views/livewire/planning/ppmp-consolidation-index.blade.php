<div>
    <x-page-header title="PPMP Consolidation" subtitle="Consolidate approved Indicative or Final PPMPs into agency-wide planning documents.">
        <x-slot:actions>
            @can('create', \App\Models\Planning\PpmpConsolidation::class)
                <x-button href="{{ route('ppmp-consolidations.create') }}">New Consolidation</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if(session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        <x-stat-card label="Approved PPMPs" :value="number_format($stats['total_ppmps_submitted'])" icon="document-text" accent="indigo" />
        <x-stat-card label="Consolidated" :value="number_format($stats['consolidated'])" icon="clipboard" accent="emerald" />
        <x-stat-card label="Pending" :value="number_format($stats['pending'])" icon="clock" accent="amber" />
        <x-stat-card label="Pending Approvals" :value="number_format($stats['pending_approvals'])" icon="shield-check" accent="sky" />
        <x-stat-card label="Total Budget" :value="'₱'.number_format($stats['total_budget'], 2)" icon="banknotes" />
    </div>

    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Budget by Division">
            @forelse($budgetByDivision as $division => $amount)
                <div class="flex items-center justify-between border-b border-slate-100 py-2 text-sm last:border-0 dark:border-slate-800">
                    <span class="text-slate-600 dark:text-slate-300">{{ $division }}</span>
                    <span class="font-medium text-slate-900 dark:text-white">₱{{ number_format($amount, 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">No consolidated budget data yet.</p>
            @endforelse
        </x-card>
        <x-card title="Budget by Fund Source">
            @forelse($budgetByFund as $fund => $amount)
                <div class="flex items-center justify-between border-b border-slate-100 py-2 text-sm last:border-0 dark:border-slate-800">
                    <span class="text-slate-600 dark:text-slate-300">{{ $fund }}</span>
                    <span class="font-medium text-slate-900 dark:text-white">₱{{ number_format($amount, 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">No fund source breakdown yet.</p>
            @endforelse
        </x-card>
    </div>

    <x-card title="Consolidations">
        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <select wire:model.live="fiscalYearId" class="rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                <option value="">All fiscal years</option>
                @foreach($fiscalYears as $fy)
                    <option value="{{ $fy->id }}">{{ $fy->year }}</option>
                @endforeach
            </select>
            <select wire:model.live="documentType" class="rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                <option value="">All PPMP types</option>
                @foreach($documentTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="status" class="rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                <option value="">All statuses</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">Reference</th>
                        <th class="py-2 pr-4">Title</th>
                        <th class="py-2 pr-4">Type</th>
                        <th class="py-2 pr-4">PPMPs</th>
                        <th class="py-2 pr-4 text-right">Budget</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Updated</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($consolidations as $record)
                        <tr>
                            <td class="py-2.5 pr-4 font-medium">{{ $record->reference_no }}</td>
                            <td class="py-2.5 pr-4">{{ $record->title }}</td>
                            <td class="py-2.5 pr-4">{{ $record->document_type?->label() }}</td>
                            <td class="py-2.5 pr-4">{{ $record->source_ppmps_count }} / {{ $record->items_count }} items</td>
                            <td class="py-2.5 pr-4 text-right">₱{{ number_format($record->total_budget, 2) }}</td>
                            <td class="py-2.5 pr-4"><x-status-badge :status="$record->status" /></td>
                            <td class="py-2.5 pr-4 text-slate-500">{{ $record->updated_at->format('M d, Y') }}</td>
                            <td class="py-2.5 text-right">
                                <div class="flex justify-end gap-2">
                                    <x-button href="{{ route('ppmp-consolidations.wizard', $record) }}" variant="secondary" size="sm">Open</x-button>
                                    @can('cancel', $record)
                                        <x-button type="button" wire:click="promptCancel('{{ $record->id }}')" variant="danger" size="sm">Cancel</x-button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">No consolidations yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $consolidations->links() }}</div>
    </x-card>

    @if($cancelConsolidationId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-red-700 dark:text-red-400">Cancel Consolidation</h3>
                <p class="mt-1 text-sm text-slate-500">Source PPMPs will be released for a new consolidation.</p>
                <textarea wire:model="cancelRemarks" rows="3" placeholder="Reason for cancellation (required)" class="mt-3 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
                @error('cancelRemarks') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <div class="mt-4 flex justify-end gap-2">
                    <x-button wire:click="$set('cancelConsolidationId', null)" variant="secondary">Close</x-button>
                    <x-button wire:click="cancelConsolidation" variant="danger" wire:confirm="Cancel this PPMP consolidation?">Confirm Cancel</x-button>
                </div>
            </div>
        </div>
    @endif
</div>
