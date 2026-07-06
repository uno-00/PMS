<div>
    <x-page-header title="Project Procurement Management Plans" subtitle="2026 Government PPMP format &mdash; division-level annual procurement planning.">
        <x-slot:actions>
            @can('create', \App\Models\Planning\Ppmp::class)
                <x-button href="{{ route('ppmps.create') }}"><x-icon name="plus" class="h-4 w-4" /> New PPMP</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="mb-4 flex gap-2">
        <select wire:model.live="status" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <option value="">All statuses</option>
            @foreach(\App\Enums\PpmpStatus::cases() as $case)
                <option value="{{ $case->value }}">{{ $case->label() }}</option>
            @endforeach
        </select>
    </div>

    @if($ppmps->isEmpty())
        <x-empty-state icon="document-text" title="No PPMPs found" description="Create a PPMP to start planning your division's annual procurement." />
    @else
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">Control No.</th>
                            <th class="py-2 pr-4">Title</th>
                            <th class="py-2 pr-4">Division</th>
                            <th class="py-2 pr-4">FY</th>
                            <th class="py-2 pr-4 text-right">Total ABC</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($ppmps as $ppmp)
                            <tr>
                                <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $ppmp->control_no }}</td>
                                <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ $ppmp->title }}</td>
                                <td class="py-3 pr-4">{{ $ppmp->division?->name }}</td>
                                <td class="py-3 pr-4">{{ $ppmp->fiscalYear?->year }}</td>
                                <td class="py-3 pr-4 text-right">₱{{ number_format($ppmp->total_abc, 2) }}</td>
                                <td class="py-3 pr-4"><x-status-badge :status="$ppmp->status" /></td>
                                <td class="py-3 pr-4 text-right"><x-button href="{{ route('ppmps.show', $ppmp) }}" variant="secondary" size="sm">View</x-button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $ppmps->links() }}</div>
        </x-card>
    @endif
</div>
