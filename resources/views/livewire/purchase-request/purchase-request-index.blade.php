<div>
    <x-page-header title="Purchase Requests" subtitle="Requests drawn from approved PPMPs, with automatic PPMP balance deduction.">
        <x-slot:actions>
            @can('create', \App\Models\Procurement\PurchaseRequest::class)
                <x-button href="{{ route('purchase-requests.create') }}"><x-icon name="plus" class="h-4 w-4" /> New Purchase Request</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="mb-4 flex gap-2">
        <select wire:model.live="status" class="rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <option value="">All statuses</option>
            @foreach(\App\Enums\PurchaseRequestStatus::cases() as $case)
                <option value="{{ $case->value }}">{{ $case->label() }}</option>
            @endforeach
        </select>
    </div>

    @if($prs->isEmpty())
        <x-empty-state icon="shopping-cart" title="No purchase requests found" />
    @else
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">PR No.</th>
                            <th class="py-2 pr-4">Purpose</th>
                            <th class="py-2 pr-4">Division</th>
                            <th class="py-2 pr-4 text-right">Amount</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($prs as $pr)
                            <tr>
                                <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $pr->pr_no }}</td>
                                <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ \Illuminate\Support\Str::limit($pr->purpose, 40) }}</td>
                                <td class="py-3 pr-4">{{ $pr->division?->name }}</td>
                                <td class="py-3 pr-4 text-right">₱{{ number_format($pr->total_amount, 2) }}</td>
                                <td class="py-3 pr-4"><x-status-badge :status="$pr->status" /></td>
                                <td class="py-3 pr-4 text-right"><x-button href="{{ route('purchase-requests.show', $pr) }}" variant="secondary" size="sm">View</x-button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $prs->links() }}</div>
        </x-card>
    @endif
</div>
