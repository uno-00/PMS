<div>
    <x-page-header title="Procurement Cases" subtitle="BAC-handled cases from planning through PhilGEPS posting, bidding, award, and PO issuance." />

    <div class="mb-4 flex gap-2">
        <select wire:model.live="status" class="rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <option value="">All statuses</option>
            @foreach(\App\Enums\ProcurementCaseStatus::cases() as $case)
                <option value="{{ $case->value }}">{{ $case->label() }}</option>
            @endforeach
        </select>
    </div>

    @if($procurements->isEmpty())
        <x-empty-state icon="briefcase" title="No procurement cases yet" description="Cases are opened from an approved Purchase Request." />
    @else
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">Case No.</th>
                            <th class="py-2 pr-4">Title</th>
                            <th class="py-2 pr-4">Mode</th>
                            <th class="py-2 pr-4 text-right">ABC</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($procurements as $case)
                            <tr>
                                <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $case->case_no }}</td>
                                <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ $case->title }}</td>
                                <td class="py-3 pr-4">{{ $case->modeOfProcurement?->name }}</td>
                                <td class="py-3 pr-4 text-right">₱{{ number_format($case->abc, 2) }}</td>
                                <td class="py-3 pr-4"><x-status-badge :status="$case->status" /></td>
                                <td class="py-3 pr-4 text-right"><x-button href="{{ route('procurements.show', $case) }}" variant="secondary" size="sm">View</x-button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $procurements->links() }}</div>
        </x-card>
    @endif
</div>
