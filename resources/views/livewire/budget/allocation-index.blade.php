<div>
    <x-page-header title="Budget Allocation" subtitle="Allocate budget per Division, Office, and Cost Center. No overallocation is ever permitted.">
        <x-slot:actions>
            @can('create', \App\Models\Budget\BudgetAllocation::class)
                <x-button href="{{ route('budget-allocations.create') }}" size="sm"><x-icon name="plus" class="h-4 w-4" /> New Allocation</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <x-table.filter-toolbar>Use the column filters below to search budget allocation records.</x-table.filter-toolbar>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">FY</th>
                        <th class="py-2 pr-4">Level</th>
                        <th class="py-2 pr-4">Organizational Unit</th>
                        <th class="py-2 pr-4">PAP / Fund Source</th>
                        <th class="py-2 pr-4 text-right">Allocated</th>
                        <th class="py-2 pr-4 text-right">Utilized</th>
                        <th class="py-2 pr-4 text-right">Remaining</th>
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="fiscalYearId" placeholder="All FY">
                                @foreach($fiscalYears as $fy)
                                    <option value="{{ $fy->id }}">FY {{ $fy->year }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterLevel">
                                <option value="division">Division</option>
                                <option value="office">Office</option>
                                <option value="cost_center">Cost Center</option>
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterOrgUnit" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterPap" placeholder="PAP code…" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterAllocated" placeholder="Amount…" align="right" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterUtilized" placeholder="Amount…" align="right" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterRemaining" placeholder="Amount…" align="right" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($allocations as $alloc)
                        <tr wire:key="allocation-{{ $alloc->id }}">
                            <td class="py-2.5 pr-4 text-xs text-slate-500">{{ $alloc->fiscalYear?->year }}</td>
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
                            <td class="py-2.5 pr-4 text-xs text-slate-500">{{ $alloc->created_at?->format('M d, Y') }}</td>
                            <td class="py-2.5 pr-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    @can('budget-allocation.allocate')
                                        @if($alloc->remaining_balance > 0)
                                            <button wire:click="openAllocateModal('{{ $alloc->id }}')" class="text-xs font-medium text-primary-700 hover:underline dark:text-primary-400">Sub-allocate</button>
                                        @endif
                                    @endcan
                                    @can('update', $alloc)
                                        <a href="{{ route('budget-allocations.edit', $alloc) }}" class="text-xs font-medium text-slate-600 hover:underline dark:text-slate-300">Edit</a>
                                    @endcan
                                    @can('delete', $alloc)
                                        <x-button wire:click="delete('{{ $alloc->id }}')" wire:confirm="Delete this budget allocation? This is only allowed for leaf nodes with no utilized funds." variant="danger" size="sm" title="Delete"><x-icon name="trash" class="h-4 w-4" /></x-button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                No records match your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

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
