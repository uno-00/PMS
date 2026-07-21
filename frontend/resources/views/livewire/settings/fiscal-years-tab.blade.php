<div>
    <div class="flex items-center justify-between">
        <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Fiscal Years</h3>
        @can('fiscal-year.create')
            <x-button size="sm" wire:click="openCreateModal">Create Fiscal Year</x-button>
        @endcan
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <x-card class="mt-4">
        <x-table.filter-toolbar>Use the column filters below to search fiscal year records.</x-table.filter-toolbar>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">Year</th>
                        <th class="py-2 pr-4">Period</th>
                        <th class="py-2 pr-4">GAA</th>
                        <th class="py-2 pr-4">APP</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Current</th>
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterYear" placeholder="Year…" /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterHasGaa" placeholder="All">
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterHasApp" placeholder="All">
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterStatus">
                                @foreach(\App\Enums\FiscalYearStatus::cases() as $case)
                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterIsCurrent" placeholder="All">
                                <option value="yes">Current</option>
                                <option value="no">Not current</option>
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($fiscalYears as $fy)
                        <tr wire:key="fy-{{ $fy->id }}">
                            <td class="py-2.5 pr-4 font-semibold">{{ $fy->year }}</td>
                            <td class="py-2.5 pr-4 text-xs text-slate-500">{{ $fy->start_date->format('M d, Y') }} – {{ $fy->end_date->format('M d, Y') }}</td>
                            <td class="py-2.5 pr-4">{{ $fy->gaa ? '✔' : '—' }}</td>
                            <td class="py-2.5 pr-4">{{ $fy->annualProcurementPlan ? '✔' : '—' }}</td>
                            <td class="py-2.5 pr-4"><x-status-badge :status="$fy->status" /></td>
                            <td class="py-2.5 pr-4">
                                @if($fy->is_current)
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Current</span>
                                @endif
                            </td>
                            <td class="py-2.5 pr-4 text-xs text-slate-500">{{ $fy->created_at?->format('M d, Y') }}</td>
                            <td class="py-2.5 pr-4 text-right">
                                <div class="flex justify-end gap-2">
                                    @can('fiscal-year.activate')
                                        @if(!$fy->is_current && $fy->status !== \App\Enums\FiscalYearStatus::Closed)
                                            <x-button size="sm" variant="secondary" wire:click="activate('{{ $fy->id }}')" wire:confirm="Set {{ $fy->year }} as the current fiscal year?">Activate</x-button>
                                        @endif
                                    @endcan
                                    @can('fiscal-year.edit')
                                        @if($fy->status !== \App\Enums\FiscalYearStatus::Closed)
                                            <x-button size="sm" variant="danger" wire:click="close('{{ $fy->id }}')" wire:confirm="Close fiscal year {{ $fy->year }}?">Close</x-button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                No records match your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" wire:click.self="$set('showCreateModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Create Fiscal Year</h3>
                <div class="mt-4 space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Year</label>
                        <input wire:model="year" type="number" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('year') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Start Date</label>
                        <input wire:model="start_date" type="date" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('start_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">End Date</label>
                        <input wire:model="end_date" type="date" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('end_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <x-button variant="secondary" wire:click="$set('showCreateModal', false)">Cancel</x-button>
                    <x-button wire:click="create">Create</x-button>
                </div>
            </div>
        </div>
    @endif
</div>
