<div>
    <x-page-header title="PhilGEPS Postings" subtitle="Phase 8 — every procurement case published (or manually recorded) on PhilGEPS.">
        <x-slot:actions>
            @can('reports.export')
                <x-button href="{{ route('reports.index', ['tab' => 'bac']) }}" variant="secondary" size="sm">Generate Posting Report</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="mb-4 flex gap-2">
        <select wire:model.live="status" class="rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <option value="">All statuses</option>
            @foreach($statuses as $case)
                <option value="{{ $case->value }}">{{ $case->label() }}</option>
            @endforeach
        </select>
    </div>

    @if($postings->isEmpty())
        <x-empty-state icon="globe" title="No PhilGEPS postings yet" description="Post a procurement case to PhilGEPS from its case page." />
    @else
        <x-card>
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
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($postings as $posting)
                            <tr>
                                <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $posting->reference_no ?? '—' }}</td>
                                <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ $posting->procurement->title }}</td>
                                <td class="py-3 pr-4">{{ $posting->procurement->modeOfProcurement?->name }}</td>
                                <td class="py-3 pr-4">{{ $posting->posting_date->format('M d, Y') }}</td>
                                <td class="py-3 pr-4">{{ $posting->closing_date->format('M d, Y') }}</td>
                                <td class="py-3 pr-4 text-xs">{{ $posting->is_manual ? 'Manual' : 'API' }}</td>
                                <td class="py-3 pr-4"><x-status-badge :status="$posting->status" /></td>
                                <td class="py-3 pr-4 text-right"><x-button href="{{ route('procurements.show', $posting->procurement) }}" variant="secondary" size="sm">View</x-button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $postings->links() }}</div>
        </x-card>
    @endif
</div>
