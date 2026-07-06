<div>
    <x-page-header title="Bid Document Orders" subtitle="Request, pay for, and download bidding documents." />

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    @if($orders->isEmpty())
        <x-empty-state icon="credit-card" title="No orders yet" description="Order bidding documents from an opportunity's detail page." />
    @else
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">Order No.</th>
                            <th class="py-2 pr-4">Procurement</th>
                            <th class="py-2 pr-4 text-right">Amount</th>
                            <th class="py-2 pr-4">OR No.</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($orders as $order)
                            <tr>
                                <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $order->order_no }}</td>
                                <td class="py-3 pr-4">{{ $order->procurement->title }}</td>
                                <td class="py-3 pr-4 text-right">₱{{ number_format($order->amount, 2) }}</td>
                                <td class="py-3 pr-4">{{ $order->or_no ?? '—' }}</td>
                                <td class="py-3 pr-4"><x-status-badge :status="new \App\Support\SimpleStatus($order->payment_status)" /></td>
                                <td class="py-3 pr-4 text-right">
                                    @if($order->payment_status === 'pending')
                                        <x-button wire:click="pay('{{ $order->id }}')" size="sm">Pay Now</x-button>
                                    @else
                                        <x-button href="{{ route('bidder.opportunities.show', $order->procurement) }}" variant="secondary" size="sm">Receipt / Download</x-button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $orders->links() }}</div>
        </x-card>
    @endif
</div>
