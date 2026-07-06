<div>
    <x-page-header title="Payment Monitoring" subtitle="Track disbursements against issued Purchase Orders." />

    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Payments (this page)" :value="$payments->total()" icon="credit-card" />
        <x-stat-card label="Total Processed (this page)" :value="'₱'.number_format($totalProcessed, 2)" icon="banknotes" accent="emerald" />
        <div>
            <select wire:model.live="status" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="">All statuses</option>
                <option value="processed">Processed</option>
                <option value="released">Released</option>
            </select>
        </div>
    </div>

    @if($payments->isEmpty())
        <x-empty-state icon="credit-card" title="No payments recorded yet" />
    @else
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">OR No.</th>
                            <th class="py-2 pr-4">PO No.</th>
                            <th class="py-2 pr-4">Supplier</th>
                            <th class="py-2 pr-4 text-right">Amount</th>
                            <th class="py-2 pr-4">Method</th>
                            <th class="py-2 pr-4">Date</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($payments as $payment)
                            <tr>
                                <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $payment->or_no }}</td>
                                <td class="py-3 pr-4">{{ $payment->purchaseOrder?->po_no }}</td>
                                <td class="py-3 pr-4">{{ $payment->purchaseOrder?->bidder?->company_name ?? '—' }}</td>
                                <td class="py-3 pr-4 text-right">₱{{ number_format($payment->amount, 2) }}</td>
                                <td class="py-3 pr-4">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                                <td class="py-3 pr-4">{{ $payment->payment_date?->format('M d, Y') }}</td>
                                <td class="py-3 pr-4"><x-status-badge :status="new \App\Support\SimpleStatus($payment->status)" /></td>
                                <td class="py-3 pr-4 text-right">
                                    @if($payment->purchaseOrder)
                                        <x-button href="{{ route('purchase-orders.show', $payment->purchaseOrder) }}" variant="secondary" size="sm">View PO</x-button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $payments->links() }}</div>
        </x-card>
    @endif
</div>
