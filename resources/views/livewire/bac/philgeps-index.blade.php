<div>
    <x-page-header title="PhilGEPS Postings" subtitle="Phase 8 — every procurement case published (or manually recorded) on PhilGEPS.">
        <x-slot:actions>
            @can('create', \App\Models\Bac\PhilgepsPosting::class)
                <x-button href="{{ route('philgeps.create') }}" size="sm"><x-icon name="plus" class="h-4 w-4" /> New Manual Posting</x-button>
            @endcan
            @can('reports.export')
                <x-button href="{{ route('reports.index', ['tab' => 'bac']) }}" variant="secondary" size="sm">Generate Posting Report</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <x-table.filter-toolbar>Use the column filters below to search PhilGEPS posting records.</x-table.filter-toolbar>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">Reference No.</th>
                        <th class="py-2 pr-4">Procurement</th>
                        <th class="py-2 pr-4">Mode</th>
                        <th class="py-2 pr-4">Posted</th>
                        <th class="py-2 pr-4">Closes</th>
                        <th class="py-2 pr-4">Type</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterReferenceNo" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterProcurement" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterMode" /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterType" placeholder="All types">
                                <option value="api">API</option>
                                <option value="manual">Manual</option>
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="status">
                                @foreach($statuses as $case)
                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($postings as $posting)
                        <tr wire:key="philgeps-{{ $posting->id }}">
                            <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $posting->reference_no ?? '—' }}</td>
                            <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ $posting->procurement->title }}</td>
                            <td class="py-3 pr-4">{{ $posting->procurement->modeOfProcurement?->name }}</td>
                            <td class="py-3 pr-4">{{ $posting->posting_date->format('M d, Y') }}</td>
                            <td class="py-3 pr-4">{{ $posting->closing_date->format('M d, Y') }}</td>
                            <td class="py-3 pr-4 text-xs">{{ $posting->is_manual ? 'Manual' : 'API' }}</td>
                            <td class="py-3 pr-4"><x-status-badge :status="$posting->status" /></td>
                            <td class="py-3 pr-4 text-xs text-slate-500">{{ $posting->created_at?->format('M d, Y') }}</td>
                            <td class="py-3 pr-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button href="{{ route('philgeps.show', $posting) }}" variant="secondary" size="sm">View</x-button>
                                    @can('update', $posting)
                                        <x-button href="{{ route('philgeps.edit', $posting) }}" variant="secondary" size="sm">Edit</x-button>
                                    @endcan
                                    @can('delete', $posting)
                                        <x-button wire:click="delete('{{ $posting->id }}')" wire:confirm="Remove this PhilGEPS posting?" variant="danger" size="sm" title="Delete"><x-icon name="trash" class="h-4 w-4" /></x-button>
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

        @if($postings->hasPages())
            <div class="mt-4">{{ $postings->links() }}</div>
        @endif
    </x-card>
</div>
