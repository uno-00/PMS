<div>
    <x-page-header title="Procurement Cases" subtitle="BAC-handled cases from planning through PhilGEPS posting, bidding, award, and PO issuance." />

    <x-card>
        <x-table.filter-toolbar>Use the column filters below to search procurement case records.</x-table.filter-toolbar>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">Case No.</th>
                        <th class="py-2 pr-4">Title</th>
                        <th class="py-2 pr-4">Mode</th>
                        <th class="py-2 pr-4 text-right">ABC</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterCaseNo" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterTitle" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterMode" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterAbc" placeholder="Amount…" align="right" /></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="status">
                                @foreach(\App\Enums\ProcurementCaseStatus::cases() as $case)
                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($procurements as $case)
                        <tr wire:key="procurement-{{ $case->id }}">
                            <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $case->case_no }}</td>
                            <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ $case->title }}</td>
                            <td class="py-3 pr-4">{{ $case->modeOfProcurement?->name }}</td>
                            <td class="py-3 pr-4 text-right">₱{{ number_format($case->abc, 2) }}</td>
                            <td class="py-3 pr-4"><x-status-badge :status="$case->status" /></td>
                            <td class="py-3 pr-4 text-xs text-slate-500">{{ $case->created_at?->format('M d, Y') }}</td>
                            <td class="py-3 pr-4 text-right"><x-button href="{{ route('procurements.show', $case) }}" variant="secondary" size="sm">View</x-button></td>
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

        @if($procurements->hasPages())
            <div class="mt-4">{{ $procurements->links() }}</div>
        @endif
    </x-card>
</div>
