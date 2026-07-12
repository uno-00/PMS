<div>
    <x-page-header title="Purchase Requests" subtitle="Requests drawn from approved PPMPs, with automatic PPMP balance deduction.">
        <x-slot:actions>
            @can('create', \App\Models\Procurement\PurchaseRequest::class)
                <x-button href="{{ route('purchase-requests.create') }}"><x-icon name="plus" class="h-4 w-4" /> New Purchase Request</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <x-table.filter-toolbar>Use the column filters below to search purchase request records.</x-table.filter-toolbar>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">PR No.</th>
                        <th class="py-2 pr-4">Purpose</th>
                        <th class="py-2 pr-4">Division</th>
                        <th class="py-2 pr-4 text-right">Amount</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterPrNo" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterPurpose" /></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterDivisionId">
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterAmount" placeholder="Amount…" align="right" /></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="status">
                                @foreach(\App\Enums\PurchaseRequestStatus::cases() as $case)
                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($prs as $pr)
                        <tr wire:key="pr-{{ $pr->id }}">
                            <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $pr->pr_no }}</td>
                            <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ \Illuminate\Support\Str::limit($pr->purpose, 40) }}</td>
                            <td class="py-3 pr-4">{{ $pr->division?->name }}</td>
                            <td class="py-3 pr-4 text-right">₱{{ number_format($pr->total_amount, 2) }}</td>
                            <td class="py-3 pr-4"><x-status-badge :status="$pr->status" /></td>
                            <td class="py-3 pr-4 text-xs text-slate-500">{{ $pr->created_at?->format('M d, Y') }}</td>
                            <td class="py-3 pr-4 text-right"><x-button href="{{ route('purchase-requests.show', $pr) }}" variant="secondary" size="sm">View</x-button></td>
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

        @if($prs->hasPages())
            <div class="mt-4">{{ $prs->links() }}</div>
        @endif
    </x-card>
</div>
