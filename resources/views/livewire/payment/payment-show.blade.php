<div>
    <x-page-header :title="'Payment '.($payment->or_no ?: '· '.$payment->id)" :subtitle="$payment->purchaseOrder?->po_no">
        <x-slot:actions>
            <x-status-badge :status="new \App\Support\SimpleStatus($payment->status)" class="!text-sm" />
            @can('update', $payment)
                <x-button href="{{ route('payments.edit', $payment) }}" variant="secondary">Edit</x-button>
            @endcan
            <x-button href="{{ route('payments.index') }}" variant="secondary">Back to Payments</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Amount" :value="'₱'.number_format($payment->amount, 2)" icon="banknotes" />
        <x-stat-card label="Method" :value="ucfirst(str_replace('_', ' ', $payment->method))" icon="credit-card" accent="indigo" />
        <x-stat-card label="Payment Date" :value="$payment->payment_date?->format('M d, Y') ?? '—'" icon="calendar" accent="amber" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Purchase Order">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-xs uppercase text-slate-400">PO No.</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $payment->purchaseOrder?->po_no ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Supplier</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $payment->purchaseOrder?->bidder?->company_name ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">PO Total</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">₱{{ number_format($payment->purchaseOrder?->total_amount ?? 0, 2) }}</dd></div>
                @if($payment->purchaseOrder)
                    <div><a href="{{ route('purchase-orders.show', $payment->purchaseOrder) }}" class="text-sm font-medium text-primary-700 hover:underline dark:text-primary-400">View Purchase Order →</a></div>
                @endif
            </dl>
        </x-card>

        <x-card title="Record details">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-xs uppercase text-slate-400">Official Receipt No.</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $payment->or_no ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Status</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ ucfirst($payment->status) }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-400">Processed by</dt><dd class="mt-0.5 text-slate-700 dark:text-slate-200">{{ $payment->processedBy?->name ?? '—' }}</dd></div>
            </dl>
        </x-card>
    </div>
</div>
