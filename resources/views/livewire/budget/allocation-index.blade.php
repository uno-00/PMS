<div>
    <x-page-header title="Budget Allocation" subtitle="Allocate budget per Division, Office, and Cost Center. No overallocation is ever permitted.">
        <x-slot:actions>
            <select wire:model.live="fiscalYearId" class="rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                @foreach($fiscalYears as $fy)
                    <option value="{{ $fy->id }}">FY {{ $fy->year }}</option>
                @endforeach
            </select>
        </x-slot:actions>
    </x-page-header>

    @if($allocations->isEmpty())
        <x-empty-state icon="chart-pie" title="No budget allocations yet" description="Approve and distribute a GAA for this fiscal year to seed department-level allocations." />
    @else
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">Level</th>
                            <th class="py-2 pr-4">Organizational Unit</th>
                            <th class="py-2 pr-4">PAP / Fund Source</th>
                            <th class="py-2 pr-4 text-right">Allocated</th>
                            <th class="py-2 pr-4 text-right">Utilized</th>
                            <th class="py-2 pr-4 text-right">Remaining</th>
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($allocations as $alloc)
                            <tr>
                                <td class="py-2.5 pr-4">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium capitalize text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ str_replace('_', ' ', $alloc->level) }}</span>
                                </td>
                                <td class="py-2.5 pr-4">
                                    <p class="font-medium text-slate-700 dark:text-slate-200">{{ $alloc->department?->name }}</p>
                                    <p class="text-xs text-slate-400">
                                        @if($alloc->division) {{ $alloc->division->name }} @endif
                                        @if($alloc->office) &rsaquo; {{ $alloc->office->name }} @endif
                                        @if($alloc->costCenter) &rsaquo; {{ $alloc->costCenter->name }} @endif
                                    </p>
                                </td>
                                <td class="py-2.5 pr-4 text-slate-500">{{ $alloc->pap?->code }} &middot; {{ $alloc->fundSource?->name }}</td>
                                <td class="py-2.5 pr-4 text-right">₱{{ number_format($alloc->allocated_amount, 2) }}</td>
                                <td class="py-2.5 pr-4 text-right">₱{{ number_format($alloc->utilized_amount, 2) }}</td>
                                <td class="py-2.5 pr-4 text-right font-medium text-emerald-600">₱{{ number_format($alloc->remaining_balance, 2) }}</td>
                                <td class="py-2.5 pr-4 text-right">
                                    @can('budget-allocation.allocate')
                                        @if($alloc->remaining_balance > 0)
                                            <button wire:click="openAllocateModal('{{ $alloc->id }}')" class="text-xs font-medium text-primary-700 hover:underline dark:text-primary-400">Sub-allocate</button>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif

    @if($showAllocateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Sub-allocate Budget</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Allocate To</label>
                        <select wire:model.live="level" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="division">Division</option>
                            <option value="office">Office</option>
                            <option value="cost_center">Cost Center</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Target</label>
                        <select wire:model="target_id" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">Select&hellip;</option>
                            @if($level === 'division')
                                @foreach($divisions as $d) <option value="{{ $d->id }}">{{ $d->name }}</option> @endforeach
                            @elseif($level === 'office')
                                @foreach($offices as $o) <option value="{{ $o->id }}">{{ $o->name }}</option> @endforeach
                            @else
                                @foreach($costCenters as $c) <option value="{{ $c->id }}">{{ $c->name }}</option> @endforeach
                            @endif
                        </select>
                        @error('target_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Amount</label>
                        <input wire:model="allocated_amount" type="number" step="0.01" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('allocated_amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Remarks</label>
                        <input wire:model="remarks" type="text" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <x-button wire:click="$set('showAllocateModal', false)" variant="secondary">Cancel</x-button>
                    <x-button wire:click="allocate">Allocate</x-button>
                </div>
            </div>
        </div>
    @endif
</div>
