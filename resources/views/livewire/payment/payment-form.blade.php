@php
    $field = 'mt-1.5 block w-full rounded-lg border-slate-300 px-3 py-2.5 text-base shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white sm:text-sm';
    $label = 'block text-sm font-medium text-slate-700 dark:text-slate-300';
    $isEdit = isset($payment) && $payment && $payment->exists;
@endphp

<div class="pb-28 lg:pb-0">
    <x-page-header
        :title="$isEdit ? 'Edit Payment' : 'New Payment'"
        subtitle="Record a disbursement against an issued Purchase Order."
    >
        @if($isEdit)
            <x-slot:actions>
                <x-button href="{{ route('payments.show', $payment) }}" variant="secondary" size="sm">Back</x-button>
            </x-slot:actions>
        @endif
    </x-page-header>

    <form wire:submit="save" class="space-y-5 sm:space-y-6">
        <x-card title="Payment details">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="purchase_order_id" class="{{ $label }}">Purchase Order</label>
                    <select id="purchase_order_id" wire:model="purchase_order_id" class="{{ $field }}">
                        <option value="">Select a Purchase Order…</option>
                        @foreach($purchaseOrders as $po)
                            <option value="{{ $po->id }}">{{ $po->po_no }} — {{ $po->bidder?->company_name ?? 'Unknown supplier' }} (₱{{ number_format($po->total_amount, 2) }})</option>
                        @endforeach
                    </select>
                    @error('purchase_order_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="or_no" class="{{ $label }}">Official Receipt No.</label>
                    <input id="or_no" type="text" wire:model="or_no" class="{{ $field }}" placeholder="e.g. OR-00012345" />
                    @error('or_no') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="amount" class="{{ $label }}">Amount (₱)</label>
                    <input id="amount" type="number" step="0.01" min="0.01" wire:model="amount" class="{{ $field }}" />
                    @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="payment_date" class="{{ $label }}">Payment Date</label>
                    <input id="payment_date" type="date" wire:model="payment_date" class="{{ $field }}" />
                    @error('payment_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="method" class="{{ $label }}">Method</label>
                    <select id="method" wire:model="method" class="{{ $field }}">
                        @foreach($methods as $method)
                            <option value="{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
                        @endforeach
                    </select>
                    @error('method') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="status" class="{{ $label }}">Status</label>
                    <select id="status" wire:model="status" class="{{ $field }}">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </x-card>

        <div class="flex justify-end gap-2">
            @if($isEdit)
                <x-button href="{{ route('payments.show', $payment) }}" variant="secondary">Cancel</x-button>
            @endif
            <x-button type="submit">Save Payment</x-button>
        </div>
    </form>
</div>
