<div>
    <x-page-header :title="$purchaseOrder->po_no" :subtitle="$purchaseOrder->bidder?->company_name ?? 'Supplier not yet assigned'">
        <x-slot:actions>
            <x-status-badge :status="$purchaseOrder->status" class="!text-sm" />

            @if($purchaseOrder->status === \App\Enums\PurchaseOrderStatus::Draft)
                @can('purchase-order.approve')
                    <x-button wire:click="approve" variant="success" size="sm">Approve</x-button>
                @endcan
            @endif

            @if($purchaseOrder->status === \App\Enums\PurchaseOrderStatus::Approved)
                @can('delivery.record')
                    <x-button wire:click="openModal('delivery')" size="sm">Record Delivery</x-button>
                @endcan
            @endif

            @if($purchaseOrder->status === \App\Enums\PurchaseOrderStatus::Accepted)
                @can('payment.process')
                    <x-button wire:click="openModal('payment')" size="sm">Record Payment</x-button>
                @endcan
            @endif

            @can('purchase-order.view')
                <x-button href="{{ route('purchase-orders.print', $purchaseOrder) }}" variant="secondary" size="sm"><x-icon name="printer" class="h-4 w-4" /> Print</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Total Amount" :value="'₱'.number_format($purchaseOrder->total_amount, 2)" icon="banknotes" />
        <x-stat-card label="Delivery Date" :value="$purchaseOrder->delivery_date?->format('M d, Y') ?? '—'" icon="calendar" accent="indigo" />
        <x-stat-card label="Delivery Place" :value="$purchaseOrder->delivery_place ?? '—'" icon="truck" accent="amber" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Items">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead>
                            <tr class="text-left text-xs uppercase text-slate-400">
                                <th class="py-2 pr-4">Item</th>
                                <th class="py-2 pr-4 text-right">Qty</th>
                                <th class="py-2 pr-4 text-right">Unit Cost</th>
                                <th class="py-2 pr-4 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="py-2.5 pr-4">
                                        <p class="font-medium text-slate-700 dark:text-slate-200">{{ $item->item_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $item->description }}</p>
                                    </td>
                                    <td class="py-2.5 pr-4 text-right">{{ rtrim(rtrim($item->quantity, '0'), '.') }} {{ $item->unit }}</td>
                                    <td class="py-2.5 pr-4 text-right">₱{{ number_format($item->unit_cost, 2) }}</td>
                                    <td class="py-2.5 pr-4 text-right font-medium">₱{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-200 text-sm font-semibold dark:border-slate-800">
                                <td colspan="3" class="py-2 pr-4 text-right">Total</td>
                                <td class="py-2 pr-4 text-right">₱{{ number_format($purchaseOrder->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>

            <x-card title="Delivery → Inspection → Acceptance">
                @forelse($purchaseOrder->deliveries as $delivery)
                    <div class="border-b border-slate-100 py-3 last:border-0 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Delivery {{ $delivery->delivery_receipt_no ?? '—' }}</p>
                                <p class="text-xs text-slate-400">{{ $delivery->delivery_date->format('M d, Y') }} &middot; <x-status-badge :status="new \App\Support\SimpleStatus($delivery->status)" /></p>
                            </div>
                            <div class="flex gap-2">
                                @if(!$delivery->inspection)
                                    @can('inspection.conduct')
                                        <x-button size="sm" variant="secondary" wire:click="openModal('inspection', '{{ $delivery->id }}')">Inspect</x-button>
                                    @endcan
                                @elseif(!$delivery->acceptance && $delivery->inspection->result === 'passed')
                                    @can('acceptance.confirm')
                                        <x-button size="sm" variant="secondary" wire:click="openModal('acceptance', '{{ $delivery->id }}')">Accept</x-button>
                                    @endcan
                                @endif
                            </div>
                        </div>
                        @if($delivery->inspection)
                            <p class="mt-1 text-xs text-slate-500">Inspection: <x-status-badge :status="new \App\Support\SimpleStatus($delivery->inspection->result)" /> on {{ $delivery->inspection->inspection_date->format('M d, Y') }}</p>
                        @endif
                        @if($delivery->acceptance)
                            <p class="mt-1 text-xs text-emerald-600">Accepted on {{ $delivery->acceptance->accepted_date->format('M d, Y') }}</p>
                        @endif
                    </div>
                @empty
                    <x-empty-state icon="truck" title="No deliveries recorded yet" />
                @endforelse
            </x-card>

            <x-card title="Payments">
                @forelse($purchaseOrder->payments as $payment)
                    <div class="flex items-center justify-between border-b border-slate-100 py-3 last:border-0 dark:border-slate-800">
                        <div>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">OR No. {{ $payment->or_no }}</p>
                            <p class="text-xs text-slate-400">{{ $payment->payment_date?->format('M d, Y') }} &middot; {{ ucfirst(str_replace('_',' ', $payment->method)) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium">₱{{ number_format($payment->amount, 2) }}</p>
                            <x-status-badge :status="new \App\Support\SimpleStatus($payment->status)" />
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="credit-card" title="No payments recorded yet" />
                @endforelse
            </x-card>
        </div>

        <x-card title="Workflow History">
            <x-workflow-timeline :history="$history" />
        </x-card>
    </div>

    @if($activeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" wire:click.self="closeModal">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                @if($activeModal === 'delivery')
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Record Delivery</h3>
                    <div class="mt-4 space-y-3">
                        <input wire:model="delivery_receipt_no" type="text" placeholder="Delivery Receipt No." class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <input wire:model="delivery_date" type="date" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('delivery_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <select wire:model="delivery_status" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="delivered">Delivered</option>
                            <option value="partial">Partial</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <textarea wire:model="delivery_remarks" rows="2" placeholder="Remarks" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <x-button variant="secondary" wire:click="closeModal">Cancel</x-button>
                        <x-button wire:click="recordDelivery">Save</x-button>
                    </div>
                @elseif($activeModal === 'inspection')
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Record Inspection</h3>
                    <div class="mt-4 space-y-3">
                        <select wire:model="inspection_result" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="passed">Passed</option>
                            <option value="failed">Failed</option>
                        </select>
                        <textarea wire:model="inspection_remarks" rows="2" placeholder="Remarks" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <x-button variant="secondary" wire:click="closeModal">Cancel</x-button>
                        <x-button wire:click="recordInspection">Save</x-button>
                    </div>
                @elseif($activeModal === 'acceptance')
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Record Acceptance</h3>
                    <div class="mt-4 space-y-3">
                        <textarea wire:model="acceptance_remarks" rows="2" placeholder="Remarks" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <x-button variant="secondary" wire:click="closeModal">Cancel</x-button>
                        <x-button wire:click="recordAcceptance">Confirm Acceptance</x-button>
                    </div>
                @elseif($activeModal === 'payment')
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Record Payment</h3>
                    <div class="mt-4 space-y-3">
                        <input wire:model="or_no" type="text" placeholder="OR No." class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('or_no') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <input wire:model="payment_amount" type="number" step="0.01" placeholder="Amount" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('payment_amount') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <input wire:model="payment_date" type="date" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('payment_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <select wire:model="payment_method" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="check">Check</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="ada">ADA</option>
                        </select>
                        <select wire:model="payment_status" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="processed">Processed</option>
                            <option value="released">Released</option>
                        </select>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <x-button variant="secondary" wire:click="closeModal">Cancel</x-button>
                        <x-button wire:click="recordPayment">Save</x-button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
