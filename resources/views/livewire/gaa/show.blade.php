<div>
    <x-page-header :title="$gaa->title" :subtitle="'Reference No. '.($gaa->reference_no ?? '—')">
        <x-slot:actions>
            <x-status-badge :status="$gaa->status" class="!text-sm" />
            @can('validateBudget', $gaa)
                @if($gaa->status === \App\Enums\GaaStatus::Draft)
                    <x-button wire:click="validateBudget" wire:loading.attr="disabled">Validate Budget</x-button>
                @endif
            @endcan
            @if($gaa->status === \App\Enums\GaaStatus::Validated || $gaa->status === \App\Enums\GaaStatus::Approved)
                <x-button wire:click="compareBudget" variant="secondary">Compare vs. Prior Year</x-button>
            @endif
            @can('approve', $gaa)
                @if($gaa->status === \App\Enums\GaaStatus::Validated)
                    <x-button wire:click="approve" variant="success">Approve</x-button>
                @endif
            @endcan
            @can('distribute', $gaa)
                @if($gaa->status === \App\Enums\GaaStatus::Approved)
                    <x-button wire:click="distribute" variant="success">Generate &amp; Distribute Allocation</x-button>
                @endif
            @endcan
        </x-slot:actions>
    </x-page-header>

    @error('validation')
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ $message }}</div>
    @enderror
    @error('distribute')
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ $message }}</div>
    @enderror

    @if(!empty($gaa->validation_errors))
        <div class="mb-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
            <p class="font-semibold">Validation issues found:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach($gaa->validation_errors as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total Amount" :value="'₱'.number_format($gaa->total_amount, 2)" icon="banknotes" />
        <x-stat-card label="Line Items" :value="$gaa->lineItems()->count()" icon="document-text" accent="indigo" />
        <x-stat-card label="Uploaded By" :value="$gaa->uploader?->name ?? '—'" icon="check-badge" accent="amber" />
        <x-stat-card label="Fiscal Year" :value="$gaa->fiscalYear->year" icon="calendar" accent="emerald" />
    </div>

    <x-card title="Budget Line Items" class="mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">#</th>
                        <th class="py-2 pr-4">Department / Division</th>
                        <th class="py-2 pr-4">PAP</th>
                        <th class="py-2 pr-4">UACS</th>
                        <th class="py-2 pr-4">Fund Source</th>
                        <th class="py-2 pr-4">Description</th>
                        <th class="py-2 pr-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($lineItems as $item)
                        <tr>
                            <td class="py-2.5 pr-4 text-slate-400">{{ $item->line_no }}</td>
                            <td class="py-2.5 pr-4">{{ $item->department?->name }} @if($item->division)<br><span class="text-xs text-slate-400">{{ $item->division->name }}</span>@endif</td>
                            <td class="py-2.5 pr-4">{{ $item->pap?->code }}</td>
                            <td class="py-2.5 pr-4">{{ $item->uacsCode?->code }}</td>
                            <td class="py-2.5 pr-4">{{ $item->fundSource?->name }}</td>
                            <td class="py-2.5 pr-4 text-slate-500">{{ $item->description }}</td>
                            <td class="py-2.5 pr-4 text-right font-medium">₱{{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 text-center text-slate-400">No line items parsed.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $lineItems->links() }}</div>
    </x-card>

    @if($showCompare)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" wire:click.self="$set('showCompare', false)">
            <div class="w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Budget Comparison vs. Prior Year</h3>
                    <button wire:click="$set('showCompare', false)" class="text-slate-400 hover:text-slate-600"><x-icon name="x-mark" class="h-5 w-5" /></button>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead>
                            <tr class="text-left text-xs uppercase text-slate-400">
                                <th class="py-2 pr-4">PAP</th>
                                <th class="py-2 pr-4 text-right">Current</th>
                                <th class="py-2 pr-4 text-right">Previous</th>
                                <th class="py-2 pr-4 text-right">Variance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($comparison as $row)
                                <tr>
                                    <td class="py-2 pr-4">{{ \App\Models\Settings\Pap::find($row['pap_id'])?->code ?? '—' }}</td>
                                    <td class="py-2 pr-4 text-right">₱{{ number_format($row['current'], 2) }}</td>
                                    <td class="py-2 pr-4 text-right">₱{{ number_format($row['previous'], 2) }}</td>
                                    <td class="py-2 pr-4 text-right {{ $row['variance'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $row['variance'] >= 0 ? '+' : '' }}₱{{ number_format($row['variance'], 2) }}
                                        @if($row['variance_pct'] !== null)<span class="text-xs text-slate-400">({{ $row['variance_pct'] }}%)</span>@endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-6 text-center text-slate-400">No prior year data to compare.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
