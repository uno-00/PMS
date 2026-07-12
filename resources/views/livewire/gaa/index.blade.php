<div>
    <x-page-header title="General Appropriations Act" subtitle="DBM-issued budgets per fiscal year, from upload through distribution.">
        <x-slot:actions>
            @can('upload', \App\Models\Budget\GeneralAppropriationsAct::class)
                <x-button href="{{ route('gaa.create') }}">
                    <x-icon name="plus" class="h-4 w-4" /> Upload GAA
                </x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <x-table.filter-toolbar>Use the column filters below to search GAA records.</x-table.filter-toolbar>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">Fiscal Year</th>
                        <th class="py-2 pr-4">Title</th>
                        <th class="py-2 pr-4">Reference No.</th>
                        <th class="py-2 pr-4 text-right">Total Amount</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterFiscalYearId">
                                @foreach($fiscalYears as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->year }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterTitle" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterReferenceNo" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterTotalAmount" placeholder="Amount…" align="right" /></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="status">
                                @foreach(\App\Enums\GaaStatus::cases() as $case)
                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($gaas as $gaa)
                        <tr wire:key="gaa-{{ $gaa->id }}">
                            <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ $gaa->fiscalYear->year }}</td>
                            <td class="py-3 pr-4">{{ $gaa->title }}</td>
                            <td class="py-3 pr-4 text-slate-500">{{ $gaa->reference_no ?? '—' }}</td>
                            <td class="py-3 pr-4 text-right">₱{{ number_format($gaa->total_amount, 2) }}</td>
                            <td class="py-3 pr-4"><x-status-badge :status="$gaa->status" /></td>
                            <td class="py-3 pr-4 text-xs text-slate-500">{{ $gaa->created_at?->format('M d, Y') }}</td>
                            <td class="py-3 pr-4 text-right">
                                <x-button href="{{ route('gaa.show', $gaa) }}" variant="secondary" size="sm">View</x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                No records match your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($gaas->hasPages())
            <div class="mt-4">{{ $gaas->links() }}</div>
        @endif
    </x-card>
</div>
