<div>
    <x-page-header title="Bid Document Orders" subtitle="Request, pay for, and download bidding documents." />

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <x-card>
        <x-table.filter-toolbar>Use the column filters below to search order records.</x-table.filter-toolbar>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">Order No.</th>
                        <th class="py-2 pr-4">Procurement</th>
                        <th class="py-2 pr-4 text-right">Amount</th>
                        <th class="py-2 pr-4">OR No.</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterOrderNo" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterProcurement" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterAmount" placeholder="Amount…" align="right" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterOrNo" /></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterPaymentStatus">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($orders as $order)
                        <tr wire:key="order-{{ $order->id }}">
                            <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $order->order_no }}</td>
                            <td class="py-3 pr-4">{{ $order->procurement->title }}</td>
                            <td class="py-3 pr-4 text-right">₱{{ number_format($order->amount, 2) }}</td>
                            <td class="py-3 pr-4">{{ $order->or_no ?? '—' }}</td>
                            <td class="py-3 pr-4"><x-status-badge :status="new \App\Support\SimpleStatus($order->payment_status)" /></td>
                            <td class="py-3 pr-4 text-xs text-slate-500">{{ $order->created_at?->format('M d, Y') }}</td>
                            <td class="py-3 pr-4 text-right">
                                @if($order->payment_status === 'pending')
                                    <x-button wire:click="pay('{{ $order->id }}')" size="sm">Pay Now</x-button>
                                @else
                                    <x-button href="{{ route('bidder.opportunities.show', $order->procurement) }}" variant="secondary" size="sm">Receipt / Download</x-button>
                                @endif
                            </td>
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

        @if($orders->hasPages())
            <div class="mt-4">{{ $orders->links() }}</div>
        @endif
    </x-card>
</div>
