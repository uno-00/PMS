<div>
    <x-page-header title="Market Scoping" subtitle="NGPA Market Scoping Checklist (RA 12009 IRR Section 10) for PPMP development.">
        <x-slot:actions>
            @can('create', \App\Models\Planning\MarketScoping::class)
                <x-button href="{{ route('market-scoping.create') }}"><x-icon name="plus" class="h-4 w-4" /> New Checklist</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <x-table.filter-toolbar>Use the column filters below to search market scoping records.</x-table.filter-toolbar>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">Control No.</th>
                        <th class="py-2 pr-4">Project Name</th>
                        <th class="py-2 pr-4">End-User Unit</th>
                        <th class="py-2 pr-4">FY</th>
                        <th class="py-2 pr-4 text-right">Estimated Budget</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterControlNo" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterProjectName" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterEndUserUnit" /></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterFiscalYearId">
                                @foreach($fiscalYears as $fiscalYear)
                                    <option value="{{ $fiscalYear->id }}">{{ $fiscalYear->year }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterEstimatedBudget" placeholder="Amount…" align="right" /></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="status">
                                @foreach(\App\Enums\MarketScopingStatus::cases() as $case)
                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($records as $record)
                        <tr wire:key="market-scoping-{{ $record->id }}">
                            <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $record->control_no }}</td>
                            <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ $record->project_name }}</td>
                            <td class="py-3 pr-4">{{ $record->end_user_unit ?: $record->division?->name }}</td>
                            <td class="py-3 pr-4">{{ $record->fiscalYear?->year }}</td>
                            <td class="py-3 pr-4 text-right">₱{{ number_format($record->estimated_budget, 2) }}</td>
                            <td class="py-3 pr-4"><x-status-badge :status="$record->status" /></td>
                            <td class="py-3 pr-4 text-xs text-slate-500">{{ $record->created_at?->format('M d, Y') }}</td>
                            <td class="py-3 pr-4 text-right"><x-button href="{{ route('market-scoping.show', $record) }}" variant="secondary" size="sm">View</x-button></td>
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

        @if($records->hasPages())
            <div class="mt-4">{{ $records->links() }}</div>
        @endif
    </x-card>
</div>
