<div>
    <x-page-header title="Purchase Orders" subtitle="Phase 17 — issued for Small Value Procurement, Shopping, Direct Contracting, or after Notice to Proceed.">
        <x-slot:actions>
            @can('purchase-order.create')
                <x-button wire:click="openCreateModal">Create Purchase Order</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <x-card>
        <x-table.filter-toolbar>Use the column filters below to search purchase order records.</x-table.filter-toolbar>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">PO No.</th>
                        <th class="py-2 pr-4">Supplier</th>
                        <th class="py-2 pr-4">Division</th>
                        <th class="py-2 pr-4 text-right">Amount</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterPoNo" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterSupplier" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterDivision" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterAmount" placeholder="Amount…" align="right" /></th>
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
                    @forelse($purchaseOrders as $po)
                        <tr wire:key="po-{{ $po->id }}">
                            <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $po->po_no }}</td>
                            <td class="py-3 pr-4">{{ $po->bidder?->company_name ?? '—' }}</td>
                            <td class="py-3 pr-4">{{ $po->purchaseRequest?->division?->name }}</td>
                            <td class="py-3 pr-4 text-right">₱{{ number_format($po->total_amount, 2) }}</td>
                            <td class="py-3 pr-4"><x-status-badge :status="$po->status" /></td>
                            <td class="py-3 pr-4 text-xs text-slate-500">{{ $po->created_at?->format('M d, Y') }}</td>
                            <td class="py-3 pr-4 text-right"><x-button href="{{ route('purchase-orders.show', $po) }}" variant="secondary" size="sm">View</x-button></td>
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

        @if($purchaseOrders->hasPages())
            <div class="mt-4">{{ $purchaseOrders->links() }}</div>
        @endif
    </x-card>

    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" wire:click.self="$set('showCreateModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Create Purchase Order</h3>
                <div class="mt-4 space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Approved Purchase Request</label>
                        <select wire:model.live="purchase_request_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">Select Purchase Request</option>
                            @foreach($eligiblePrs as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->pr_no }} &middot; {{ $pr->division?->name }} &middot; ₱{{ number_format($pr->total_amount, 2) }}</option>
                            @endforeach
                        </select>
                        @error('purchase_request_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Supplier (optional)</label>
                        <select wire:model="bidder_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">Not yet assigned</option>
                            @foreach($bidders as $b)
                                <option value="{{ $b->id }}">{{ $b->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Delivery Date</label>
                        <input wire:model="delivery_date" type="date" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('delivery_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Delivery Place</label>
                        <input wire:model="delivery_place" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('delivery_place') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    @if(!empty($items))
                        <div class="max-h-40 space-y-1 overflow-y-auto rounded-lg bg-slate-50 p-2 text-xs dark:bg-slate-800">
                            @foreach($items as $item)
                                <div class="flex justify-between"><span>{{ $item['item_name'] }}</span><span>{{ $item['quantity'] }} {{ $item['unit'] }} &times; ₱{{ number_format((float) $item['unit_cost'], 2) }}</span></div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <x-button variant="secondary" wire:click="$set('showCreateModal', false)">Cancel</x-button>
                    <x-button wire:click="create">Create as Draft</x-button>
                </div>
            </div>
        </div>
    @endif
</div>
