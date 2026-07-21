<div>
    <x-page-header title="Payment Monitoring" subtitle="Track disbursements against issued Purchase Orders.">
        <x-slot:actions>
            @can('create', \App\Models\Procurement\Payment::class)
                <x-button href="{{ route('payments.create') }}" size="sm"><x-icon name="plus" class="h-4 w-4" /> New Payment</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-stat-card label="Payments (this page)" :value="$payments->total()" icon="credit-card" />
        <x-stat-card label="Total Processed (this page)" :value="'₱'.number_format($totalProcessed, 2)" icon="banknotes" accent="emerald" />
    </div>

    <x-card>
        <x-table.filter-toolbar>Use the column filters below to search payment records.</x-table.filter-toolbar>

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
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterOrNo" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterPoNo" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterSupplier" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterAmount" placeholder="Amount…" align="right" /></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterMethod">
                                <option value="check">Check</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="ada">ADA</option>
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="status">
                                <option value="pending">Pending</option>
                                <option value="processed">Processed</option>
                                <option value="released">Released</option>
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($payments as $payment)
                        <tr wire:key="payment-{{ $payment->id }}">
                            <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $payment->or_no }}</td>
                            <td class="py-3 pr-4">{{ $payment->purchaseOrder?->po_no }}</td>
                            <td class="py-3 pr-4">{{ $payment->purchaseOrder?->bidder?->company_name ?? '—' }}</td>
                            <td class="py-3 pr-4 text-right">₱{{ number_format($payment->amount, 2) }}</td>
                            <td class="py-3 pr-4">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                            <td class="py-3 pr-4">{{ $payment->payment_date?->format('M d, Y') }}</td>
                            <td class="py-3 pr-4"><x-status-badge :status="new \App\Support\SimpleStatus($payment->status)" /></td>
                            <td class="py-3 pr-4 text-xs text-slate-500">{{ $payment->created_at?->format('M d, Y') }}</td>
                            <td class="py-3 pr-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button href="{{ route('payments.show', $payment) }}" variant="secondary" size="sm">View</x-button>
                                    @can('update', $payment)
                                        <x-button href="{{ route('payments.edit', $payment) }}" variant="secondary" size="sm">Edit</x-button>
                                    @endcan
                                    @can('delete', $payment)
                                        <x-button wire:click="delete('{{ $payment->id }}')" wire:confirm="Remove this payment record?" variant="danger" size="sm" title="Delete"><x-icon name="trash" class="h-4 w-4" /></x-button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                No records match your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="mt-4">{{ $payments->links() }}</div>
        @endif
    </x-card>
</div>
