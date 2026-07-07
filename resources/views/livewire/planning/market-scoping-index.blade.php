<div>
    <x-page-header title="Market Scoping" subtitle="NGPA Market Scoping Checklist (RA 12009 IRR Section 10) for PPMP development.">
        <x-slot:actions>
            @can('create', \App\Models\Planning\MarketScoping::class)
                <x-button href="{{ route('market-scoping.create') }}"><x-icon name="plus" class="h-4 w-4" /> New Checklist</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="mb-4 flex gap-2">
        <select wire:model.live="status" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <option value="">All statuses</option>
            @foreach(\App\Enums\MarketScopingStatus::cases() as $case)
                <option value="{{ $case->value }}">{{ $case->label() }}</option>
            @endforeach
        </select>
    </div>

    @if($records->isEmpty())
        <x-empty-state icon="clipboard" title="No market scoping checklists found" description="Create a Market Scoping Checklist before developing or updating a PPMP." />
    @else
        <x-card>
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
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($records as $record)
                            <tr>
                                <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $record->control_no }}</td>
                                <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ $record->project_name }}</td>
                                <td class="py-3 pr-4">{{ $record->end_user_unit ?: $record->division?->name }}</td>
                                <td class="py-3 pr-4">{{ $record->fiscalYear?->year }}</td>
                                <td class="py-3 pr-4 text-right">₱{{ number_format($record->estimated_budget, 2) }}</td>
                                <td class="py-3 pr-4"><x-status-badge :status="$record->status" /></td>
                                <td class="py-3 pr-4 text-right"><x-button href="{{ route('market-scoping.show', $record) }}" variant="secondary" size="sm">View</x-button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $records->links() }}</div>
        </x-card>
    @endif
</div>
