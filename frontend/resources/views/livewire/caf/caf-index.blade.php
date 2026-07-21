<div>
    <x-page-header title="Certificate of Availability of Funds" subtitle="Automatically generated once a Purchase Request clears HOPE approval." />

    <x-card>
        <x-table.filter-toolbar>Use the column filters below to search CAF records.</x-table.filter-toolbar>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">CAF No.</th>
                        <th class="py-2 pr-4">Purchase Request</th>
                        <th class="py-2 pr-4">Fund Source</th>
                        <th class="py-2 pr-4 text-right">Amount</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterCafNo" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterPurchaseRequest" placeholder="PR no…" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterFundSource" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterAmount" placeholder="Amount…" align="right" /></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="status">
                                @foreach(\App\Enums\CafStatus::cases() as $case)
                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($cafs as $caf)
                        <tr wire:key="caf-{{ $caf->id }}">
                            <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $caf->caf_no }}</td>
                            <td class="py-3 pr-4">{{ $caf->purchaseRequest?->pr_no }} &middot; {{ $caf->purchaseRequest?->division?->name }}</td>
                            <td class="py-3 pr-4">{{ $caf->fundSource?->name }}</td>
                            <td class="py-3 pr-4 text-right">₱{{ number_format($caf->amount, 2) }}</td>
                            <td class="py-3 pr-4"><x-status-badge :status="$caf->status" /></td>
                            <td class="py-3 pr-4 text-xs text-slate-500">{{ $caf->created_at?->format('M d, Y') }}</td>
                            <td class="py-3 pr-4 text-right"><x-button href="{{ route('cafs.show', $caf) }}" variant="secondary" size="sm">View</x-button></td>
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

        @if($cafs->hasPages())
            <div class="mt-4">{{ $cafs->links() }}</div>
        @endif
    </x-card>
</div>
